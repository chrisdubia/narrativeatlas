<?php

class SayWhatProImporter implements SayWhatProImporterInterface {
	/**
	 * @var SayWhatProSettingsInterface
	 */
	private $settings;

	/**
	 * SayWhatProImporter constructor.
	 *
	 * @param SayWhatProSettingsInterface $settings
	 */
	public function __construct( SayWhatProSettingsInterface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Process the file for import.
	 *
	 * @param $filename
	 *
	 * @return array Response indicated success, errors, and/or success message.
	 */
	public function import_file( $filename ) {
		$response = [
			'success'         => false,
			'errors'          => [],
			'success_message' => '',
		];
		try {
			$file_handle = fopen( $filename, 'r' );
			$headers     = $this->get_headers( $file_handle );
			$inserted    = 0;
			$updated     = 0;
			// phpcs:disable WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			while ( $row = fgetcsv( $file_handle ) ) {
				// phpcs:enable
				$result = $this->process_row( $row, $headers );
				if ( 'inserted' === $result ) {
					$inserted ++;
				} elseif ( 'updated' === $result ) {
					$updated ++;
				}
			}
			fclose( $file_handle );
		} catch ( \Exception $e ) {
			$response['errors'][] = $e->getMessage();

			return $response;
		}
		$response['success'] = true;

		$status_summary = sprintf(
			// Translators: %1$d is number of inserted records, %2$d is the number of updated records.
			esc_html__( 'new records: %1$d, updates: %2$d', 'say_what' ),
			$inserted,
			$updated
		);

		$response['success_message'] = sprintf(
			// Translators: %1$d is number of inserted records, %2$d is the number of updated records.
			esc_html__( 'Number of items imported: %1$d (%2$s).', 'say_what' ),
			$inserted + $updated,
			$status_summary
		);

		return $response;
	}

	/**
	 * Process a row of the CSV file.
	 *
	 * If there is an ID column, and it matches an existing ID then update an existing translation.
	 * Otherwise insert a new one.
	 *
	 * @param $row       The row of data.
	 * @param $headers   The array indicating the header positions.
	 *
	 * @return string
	 * @throws Exception
	 */
	private function process_row( $row, $headers ) {

		// Pull out the values based on the CSV headings.

		//ID and language are optional.
		if ( isset( $headers['ID'] ) ) {
			$replacement_id = isset( $row[ $headers['ID'] ] ) ? $row[ $headers['ID'] ] : null;
		} else {
			$replacement_id = null;
		}
		if ( isset( $headers['Affected language'] ) ) {
			$language = isset( $row[ $headers['Affected language'] ] ) ? $row[ $headers['Affected language'] ] : '';
		} else {
			$language = '';
		}

		if ( isset( $headers['Active?'] ) ) {
			$active   = isset( $row[ $headers['Active?'] ] ) ? $row[ $headers['Active?'] ] : '';
			$disabled = ( 'Yes' === $active ) ? 0 : 1;
		} else {
			$disabled = 0;
		}

		// Everything else is mandatory and has been checked as present.
		$original    = isset( $row[ $headers['Original string'] ] ) ? $row[ $headers['Original string'] ] : '';
		$domain      = isset( $row[ $headers['Text domain'] ] ) ? $row[ $headers['Text domain'] ] : '';
		$context     = isset( $row[ $headers['Text context'] ] ) ? $row[ $headers['Text context'] ] : '';
		$replacement = isset( $row[ $headers['Replacement string'] ] ) ? $row[ $headers['Replacement string'] ] : '';

		if ( empty( $replacement_id ) ) {
			$this->settings->insert_replacement(
				$original,
				$domain,
				$context,
				$replacement,
				$disabled,
				$language
			);

			return 'inserted';
		} else {
			if ( ! is_numeric( $replacement_id ) || (int) $replacement_id !== (int) $replacement_id ) {
				// Translators: %s is the ID provided in the import file.
				throw new \Exception( sprintf( esc_html__( 'Invalid ID received ("%s"), processing stopped.', 'say_what' ), $replacement_id ) );
			}
			if ( $this->settings->has_id( $replacement_id ) ) {
				$this->settings->update_replacement(
					$replacement_id,
					$original,
					$domain,
					$context,
					$replacement,
					$disabled,
					$language
				);

				return 'updated';
			} else {
				$this->settings->insert_replacement(
					$original,
					$domain,
					$context,
					$replacement,
					$disabled,
					$language
				);

				return 'inserted';
			}
		}
	}

	/**
	 * Read a row from the file handle, and extract required headers, or throw exception.
	 *
	 * @param $file_handle
	 *
	 * @return array
	 * @throws Exception
	 */
	private function get_headers( $file_handle ) {
		// Skip the first row as it should be headers only.
		$headers = fgetcsv( $file_handle );
		// Check we have the relevant fields.
		$missing = [];
		foreach ( [ 'Original string', 'Text domain', 'Text context', 'Replacement string' ] as $required_header ) {
			if ( ! in_array( $required_header, $headers, true ) ) {
				$missing[] = $required_header;
			}
		}
		if ( ! empty( $missing ) ) {
			// Translators: %s is the name of the missing header(s).
			throw new \Exception( sprintf( esc_html__( 'File missing required headers: %s.', 'say_what' ), implode( ', ', $missing ) ) );
		}

		return array_flip( $headers );
	}
}
