function CMMRM_RouteRendererEditor(widget, routeModel) {
	CMMRM_RouteRenderer.apply(this, arguments);
}

CMMRM_RouteRendererEditor.prototype = Object.create(CMMRM_RouteRenderer.prototype);
CMMRM_RouteRendererEditor.prototype.contructor = CMMRM_RouteRenderer;

CMMRM_RouteRendererEditor.prototype.getStrokeWeight = function() {
	return CMMRM_Map_Settings.editorMapPolylineStrokeWeight;
};