/**
 * WP Crawl Intelligence Admin JS
 */

function renderSimpleChart( container, labels, botData, humanData = [] ) {
	const width = container.clientWidth || 800;
	const height = 250;
	const padding = 40;

	const maxVal = Math.max( ...botData, ...humanData, 10 );
	const stepX = ( width - ( padding * 2 ) ) / ( labels.length - 1 );
	
	const getPoints = ( data ) => {
		let pts = '';
		for ( let i = 0; i < data.length; i++ ) {
			const x = padding + ( i * stepX );
			const y = height - padding - ( ( data[i] / maxVal ) * ( height - ( padding * 2 ) ) );
			pts += `${x},${y} `;
		}
		return pts;
	};

	const botPoints = getPoints( botData );
	const humanPoints = humanData.length ? getPoints( humanData ) : '';

	const svg = `
		<svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}">
			<!-- Grid -->
			<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#ddd" />
			<line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" stroke="#ddd" />
			
			<!-- Human Data (Behind) -->
			${humanPoints ? `<polyline points="${humanPoints}" fill="none" stroke="#46b450" stroke-width="2" stroke-dasharray="4" />` : ''}
			
			<!-- Bot Data (Front) -->
			<polyline points="${botPoints}" fill="none" stroke="#2271b1" stroke-width="2" />
			
			<!-- Labels -->
			<text x="${padding}" y="${height - 10}" font-size="10" fill="#888">${labels[0]}</text>
			<text x="${width - padding}" y="${height - 10}" font-size="10" fill="#888" text-anchor="end">${labels[labels.length - 1]}</text>
			
			<text x="${width/2}" y="${height - 10}" font-size="10" fill="#2271b1" text-anchor="middle">Blue: Bots | Green: Human</text>
		</svg>
	`;
	
	container.outerHTML = svg;
}

document.addEventListener( 'DOMContentLoaded', function() {
	if ( ! window.wpciData ) return;
	const ctx = document.getElementById( 'wpciBotChart' );
	if ( ctx ) {
		renderSimpleChart( ctx, wpciData.labels, wpciData.botHits, wpciData.humanHits || [] );
	}
} );
