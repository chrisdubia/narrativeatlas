function CMMRM_ElevationGraphStandalone(container) {
	
	this.container = jQuery(container);
	
	this.results = null;
	
	this.maxElevation = 0;
	this.minElevation = 99999;
	this.elevationGain = 0;
	this.elevationDescent = 0;
	
	this.graph = null;
	this.graphData = null;
	
	this.calculateElevationAlongPath(this.getPathCoords());
	//this.initPolylineMouseEventListeners();
};

CMMRM_ElevationGraphStandalone.prototype = Object.create(CMMRM_ElevationGraph.prototype);
CMMRM_ElevationGraphStandalone.prototype.contructor = CMMRM_ElevationGraph;

CMMRM_ElevationGraphStandalone.prototype.initMapEventListeners = function() { };
CMMRM_ElevationGraphStandalone.prototype.initCustomChartMapEvents = function(container) { };

CMMRM_ElevationGraphStandalone.prototype.getGraphCanvasContainer = function() {
	return jQuery(this.container).find('.cmmrm-elevation-graph-canvas');
};

CMMRM_ElevationGraphStandalone.prototype.getGraphWrapper = function() {
	return jQuery(this.container);
};

CMMRM_ElevationGraphStandalone.prototype.getTravelMode = function() {
	return this.container.attr('data-travel-mode');
};

CMMRM_ElevationGraphStandalone.prototype.getPathColor = function() {
	return this.container.attr('data-path-color');
};

CMMRM_ElevationGraphStandalone.prototype.getPathString = function() {
	return this.container.attr('data-path');
};

CMMRM_ElevationGraphStandalone.prototype.getPathCoords = function() {
	if (typeof google != 'undefined') {
		return google.maps.geometry.encoding.decodePath(this.getPathString());
	} else {
		return [];
	}
};