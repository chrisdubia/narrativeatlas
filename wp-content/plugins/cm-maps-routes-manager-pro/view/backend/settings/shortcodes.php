<?php
use com\cminds\mapsroutesmanager\App;
?>
<?php if (App::isPro()): ?>
<p><strong>Notice: shortcodes are case-sensitive</strong></p>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-snippet]</h4>
		<span>Shows the route's snippet</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route's ID</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>params</strong> - whether to show the route params: 0 or 1</li>
			<li><strong>featured</strong> - featured image to show, one of: image, map.</li>
			<li><strong>layout</strong> - layout, one of: list, tiles</li>
			<li><strong>fancy</strong> - whether to show fancy style: 0 or 1</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-snippet id=123 featured=map fancy=1]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-map]</h4>
		<span>Shows the map of a specific route</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route's ID</li>
			<li><strong>theme</strong> - example: silver, retro, dark, night, aubergine</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>graph</strong> - whether to show an elevation graph: 1 or 0</li>
			<li><strong>params</strong> - whether to show the route's parameters: 1 or 0</li>
			<li><strong>map</strong> - whether to show the map: 1 or 0</li>
			<li><strong>topinfo</strong> - whether to show the route's information on top: 1 or 0</li>
			<li><strong>zoom</strong> - map zoom from 1 (visible entire globe) to 20 (visible buildings)</li>
			<li><strong>showdate</strong> - whether to show date: 0 or 1</li>
			<li><strong>showtitle</strong> - whether to show title: 0 or 1</li>
			<li><strong>showtravelmode</strong> - whether to show travel mode switch: 0 or 1</li>
			<li><strong>width</strong> - width of the widget (number of pixels, required numeric value)</li>
			<li><strong>mapwidth</strong> - width of the map (number of pixels, required numeric value)</li>
			<li><strong>mapheight</strong> - height of the map (number of pixels, required numeric value)</li>
			<li><strong>toolbar</strong> - whether to show the toolbar: 1 or 0 (default is 1)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-map id=123 params=0 topinfo=0 showtravelmode=0 showtitle=0 graph=1 toolbar=1]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[cm-routes-map]</h4>
		<span>Shows the map with the all routes' markers</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
            <li><strong>theme</strong> - example: silver, retro, dark, night, aubergine</li>
			<li><strong>category</strong> - category slug or ID</li>
			<li><strong>author</strong> - author's ID or slug</li>
		</ul>
		<p>You can also filter routes by custom taxonomies slugs (which you can setup on the Settings page under the Taxonomies tab),
			for example: <kbd>[cm-routes-map cmmrm_route_type=bicycle]</kbd></p>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>params</strong> - whether to show the route's parameters: 1 or 0</li>
			<li><strong>width</strong> - width of the widget (number of pixels, required numeric value)</li>
			<li><strong>mapwidth</strong> - width of the map (number of pixels, required numeric value)</li>
			<li><strong>mapheight</strong> - height of the map (number of pixels, required numeric value)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[cm-routes-map category=mountains]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[my-routes-table]</h4>
		<span>Shows the current user's routes table</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>View parameters:</h5>
		<ul>
			<li><strong>controls</strong> - whether to show the edit and delete buttons for each route: 1 or 0</li>
			<li><strong>addbtn</strong> - whether to show the "Add route" button: 1 or 0</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[my-routes-table controls=1 addbtn=1]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-author]</h4>
		<span>Shows the route's author display name.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-author id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-author-link]</h4>
		<span>Shows the link of the author's routes archive.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-author-link id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-author-url]</h4>
		<span>Shows the URL of the author's routes archive.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-author-url id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-description]</h4>
		<span>Shows the route's description.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-description id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-gallery]</h4>
		<span>Shows the route's images gallery.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-gallery id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-elevation-graph]</h4>
		<span>Shows the route's elevation graph.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-elevation-graph id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-locations]</h4>
		<span>Shows the route's locations list.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-locations id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-params]</h4>
		<span>Shows the route's parameters.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>fancy</strong> - whether to apply a fancy style (0 or 1). Default value is dependent on the plugin settings.</li>
			<li><strong>fancyborder</strong> - whether to apply a fancy border style (0 or 1). Default value is dependent on the plugin settings.</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-params id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-map-canvas]</h4>
		<span>Shows the route's map.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>zoom</strong> - map zoom from 1 (visible entire globe) to 20 (visible buildings)</li>
			<li><strong>showtravelmode</strong> - whether to show travel mode switch: 0 or 1</li>
			<li><strong>cmlocations</strong> - whether to display placemarks of the locations from <em>CM Map Locations</em> plugin,
				if you installed that one too (0 or 1).
				Default value is dependent on the plugin settings.</li>
			<li><strong>mapId</strong> - internal parameter</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-map-canvas id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-toolbar]</h4>
		<span>Shows the route's toolbar.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-toolbar id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-travel-modes]</h4>
		<span>Shows the route's travel modes switch.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-travel-modes id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-voting]</h4>
		<span>Shows the route's rating stars that allow voting.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-voting id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-categories-loop]</h4>
		<span>Shows the list of the route's categories.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
			<li><strong>tax</strong> - categories' taxonomy (default is cmmrm_category)</li>
		</ul>
		<h5>Shortcode's content (optional):</h5>
		<p>Optionally you can pass the shortcode's content as a template of a single item of the categories list
			in order to adjust it by using the internal helper shortcodes (listed below). By default it's <kbd>{link}</kbd></p>
		<h5>Internal helper shortcodes:</h5>
		<ul>
			<li><strong>{term_id}</strong> - displays term ID</li>
			<li><strong>{term_taxonomy_id}</strong> - displays term taxonomy ID</li>
			<li><strong>{name}</strong> - displays term name</li>
			<li><strong>{slug}</strong> - displays term slug</li>
			<li><strong>{description}</strong> - displays term description</li>
			<li><strong>{url}</strong> - displays an URL address to the route's archive page filtered by this category</li>
			<li><strong>{link}</strong> - displays a link to the route's archive page filtered by this category</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-categories-loop id=123]</kbd></p>
		<p><kbd>[route-categories-loop id=123 tax="route_type"]</kbd></p>
		<p><kbd>[route-categories-loop id=123]<br>
		{link}: {description}<br>
		[/route-categories-loop]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[cm-route-index]</h4>
		<span>Shows the routes index page.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
            <li><strong>theme</strong> - example: silver, retro, dark, night, aubergine</li>
			<li><strong>category</strong> - category's ID or slug</li>
			<li><strong>routetype</strong> - route type's slug</li>
			<li><strong>tag</strong> - route tag slug</li>
			<li><strong>author</strong> - author's ID or slug</li>
			<li><strong>search</strong> - filter routes by a string that will be searched in the route's title and description</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>showmap</strong> - whether to show the map (0 or 1). Default value is dependent on the plugin settings.</li>
			<li><strong>showlist</strong> - whether to show the routes list (0 or 1). Default is 1.</li>
			<li><strong>showfilters</strong> - whether to show the filtering options (0 or 1). Default is 1.</li>
			<li><strong>ajax</strong> - whether to use AJAX to move between pages and apply filters without refreshing entire page (0 or 1). Default is 1.</li>
			<li><strong>listlayout</strong> - layout of the routes' list (one of: list, tiles). Default value is dependent on the plugin settings.</li>
			<li><strong>featuredimage</strong> - what featured image to show for a route on the list (one of: image, map).
				Choose <kbd>image</kbd> to show the first image from the route's gallery.
				Choose <kbd>map</kbd> to show the route's map thumbnail.
				Default value is dependent on the plugin settings.</li>
			<li><strong>fancy</strong> - whether to apply a fancy style (0 or 1). Default value is dependent on the plugin settings.</li>
			<li><strong>limit</strong> - max number of routes to show on the list. Default value is dependent on the plugin settings.</li>
			<li><strong>cmlocations</strong> - whether to display placemarks of the locations from <em>CM Map Locations</em> plugin,
				if you installed that one too (0 or 1).
				Default value is dependent on the plugin settings.</li>
			<li><strong>width</strong> - width of the widget (number of pixels, required numeric value)</li>
			<li><strong>mapwidth</strong> - width of the map (number of pixels, required numeric value)</li>
			<li><strong>mapheight</strong> - height of the map (number of pixels, required numeric value)</li>
			<li><strong>page</strong> - page of the results for the routes list (internal parameter)</li>
			<li><strong>showonlybyparams</strong> - used for link Sharing (0 or 1)</li>
			<li><strong>usertracking</strong> - show user track location/path, 1 to show or 0 to hide</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[cm-route-index]</kbd></p>
		<p><kbd>[cm-route-index category=mtblanc showfilters=0]</kbd></p>
		<p><kbd>[cm-route-index tag=tname routetype=rtname]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-post-date]</h4>
		<span>Shows a route's created date.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-post-date id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-post-modified]</h4>
		<span>Shows a route's modified date.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-post-modified id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-permalink-url]</h4>
		<span>Shows a route's permalink URL.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-permalink-url id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-title]</h4>
		<span>Shows a route's title.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>escape</strong> - whether to escape HTML special characters (0 or 1). Default is 0</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-title id=123]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-post-data]</h4>
		<span>Shows a raw post data for a route.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
			<li><strong>col</strong> - column name (post_title, post_content etc.)</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>escape</strong> - whether to escape HTML special characters (0 or 1). Default is 0</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-post-data id=123 col=post_modified]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-post-meta]</h4>
		<span>Shows a raw post meta value for a route.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Filtering parameters:</h5>
		<ul>
			<li><strong>id</strong> - route ID (default is current route's ID)</li>
			<li><strong>key</strong> - meta_key to receive</li>
		</ul>
		<h5>View parameters:</h5>
		<ul>
			<li><strong>escape</strong> - whether to escape HTML special characters (0 or 1). Default is 0</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-post-meta id=123 key="_cmmrm_distance"]</kbd></p>
	</div>
</article>
<article class="cmmrm-shortcode-desc">
	<header>
		<h4>[route-search]</h4>
		<span>Shows a search form to find a route.</span>
	</header>
	<div class="cmmrm-shortcode-desc-inner">
		<h5>Parameters:</h5>
		<ul>
			<li><strong>categories</strong> - whether to show the categories filter (0 or 1). Default is 1.</li>
			<li><strong>customtax</strong> - whether to show the custom taxonomies filter (0 or 1). Default is 1.</li>
			<li><strong>zipcode</strong> - whether to show the zip code input field (0 or 1). Default is 1.</li>
			<li><strong>searchinput</strong> - whether to show the search text input field (0 or 1). Default is 1.</li>
			<li><strong>searchstring</strong> - set default search string entered to the search input. Default is empty.</li>
		</ul>
		<h5>Example</h5>
		<p><kbd>[route-search categories=0 customtax=0 zipcode=0 searchinput=1]</kbd> - displays only the search input field</p>
		<p><kbd>[route-search categories=0 customtax=0 zipcode=1 searchinput=0]</kbd> - displays only the zip code input field</p>
		<p><kbd>[route-search categories=1 customtax=0 zipcode=0 searchinput=0]</kbd> - displays only the list of the categories</p>
	</div>
</article>
<?php endif; ?>