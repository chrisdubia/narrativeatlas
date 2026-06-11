<?php
use com\cminds\mapsroutesmanager\helper\RouteView;

if ($images = $route->getImages()):
	RouteView::displayImages($images, 'route', $route->getId());
endif;
?>