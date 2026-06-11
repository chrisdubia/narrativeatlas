<?php

class MoBulkSMSUtils
{
    use Instance;

    function __construct()
    {
        add_action('miniorange_validation_show_addon_list',[$this,'showAddOnList']);
    }

    function showAddOnList()
    {
        /** @var AddOnList $addonList */
        $addonList = AddOnList::instance();
        $addonList = $addonList->getList();

        /** @var  BaseAddOnHandler  $addon  */
        foreach ($addonList as $addon) {
            echo    '<tr>
                    <td class="addon-table-list-status">
                        '.$addon->getAddOnName().'
                    </td>
                    <td class="addon-table-list-name">
                        <i>
                            '.$addon->getAddOnDesc().'
                        </i>
                    </td>
                    <td class="addon-table-list-actions">
                        <a  class="button-primary button tips" 
                            href="'.$addon->getSettingsUrl().'">
                            '.mo_("Settings").'
                        </a>
                    </td>
                </tr>';
        }
    }

}