var anchors     = document.getElementsByTagName( 'a' );
var anchorCount = anchors.length;
var hypRe       = new RegExp( HypothesisPDF.uploadsBase + '.+\.pdf', 'i' );
for ( i = 0; i < anchorCount; i++ ) {
	var href = anchors[i].href;
	if ( href.match( hypRe ) ) {
		anchors[i].href = 'https://via.hypothes.is/' + anchors[i].href;
	}
}
