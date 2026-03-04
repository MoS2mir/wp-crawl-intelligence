/**
 * WP Crawl Intelligence Admin JS
 */

document.addEventListener( 'DOMContentLoaded', function() {
	if ( ! window.wpciData ) {
		return;
	}

	const ctx = document.getElementById( 'wpciBotChart' );
	if ( ! ctx ) {
		return;
	}

	const labels = wpciData.labels;
	const hitCounts = wpciData.hitCounts;

	// Simple SVG chart generator
	renderSimpleChart( ctx, labels, hitCounts );
} );

function renderSimpleChart( container, labels, data ) {
	const width = container.clientWidth || 800;
	const height = 250;
	const padding = 40;

	const maxVal = Math.max( ...data ) || 10;
	const stepX = ( width - ( padding * 2 ) ) / ( labels.length - 1 );
	
	let points = '';
	for ( let i = 0; i < data.length; i++ ) {
		const x = padding + ( i * stepX );
		const y = height - padding - ( ( data[i] / maxVal ) * ( height - ( padding * 2 ) ) );
		points += `${x},${y} `;
	}

	const svg = `
		<svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}">
			<!-- Grid -->
			<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#ddd" />
			<line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" stroke="#ddd" />
			
			<!-- Data Path -->
			<polyline points="${points}" fill="none" stroke="#2271b1" stroke-width="2" />
			
			<!-- Data Points -->
			${data.map( ( val, i ) => {
				const x = padding + ( i * stepX );
				const y = height - padding - ( ( val / maxVal ) * ( height - ( padding * 2 ) ) );
				return `<circle cx="${x}" cy="${y}" r="4" fill="#2271b1" title="${labels[i]}: ${val}" />`;
			} ).join('')}

			<!-- Labels (Simplified) -->
			<text x="${padding}" y="${height - 10}" font-size="10" fill="#888">${labels[0]}</text>
			<text x="${width - padding}" y="${height - 10}" font-size="10" fill="#888" text-anchor="end">${labels[labels.length - 1]}</text>
		</svg>
	`;
	
	container.outerHTML = svg;
}
