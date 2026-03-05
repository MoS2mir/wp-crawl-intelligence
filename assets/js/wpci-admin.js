/**
 * WP Crawl Intelligence - Premium SaaS Chart Engine
 */

function renderSimpleChart(container, labels, botData, humanData = []) {
	const width = container.clientWidth || 1000;
	const height = 260;
	const padding = 50;

	const maxVal = Math.max(...botData, ...humanData, 10) * 1.1; // 10% headroom
	const stepX = (width - (padding * 2)) / (labels.length - 1);

	// Function to generate smooth path (simplified Bezier)
	const getCurvedPoints = (data) => {
		let pts = `M ${padding},${height - padding - ((data[0] / maxVal) * (height - (padding * 2)))}`;
		for (let i = 1; i < data.length; i++) {
			const x = padding + (i * stepX);
			const y = height - padding - ((data[i] / maxVal) * (height - (padding * 2)));
			const prevX = padding + ((i - 1) * stepX);
			const prevY = height - padding - ((data[i - 1] / maxVal) * (height - (padding * 2)));
			pts += ` C ${prevX + (x - prevX) / 2},${prevY} ${prevX + (x - prevX) / 2},${y} ${x},${y}`;
		}
		return pts;
	};

	const botPath = getCurvedPoints(botData);
	const humanPath = humanData.length ? getCurvedPoints(humanData) : '';

	// Gradient areas
	const getAreaPath = (path, data) => {
		const lastX = padding + ((data.length - 1) * stepX);
		return `${path} L ${lastX},${height - padding} L ${padding},${height - padding} Z`;
	};

	const svg = `
        <svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}" style="overflow: visible;">
            <defs>
                <linearGradient id="botGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.3"/>
                    <stop offset="100%" stop-color="#4f46e5" stop-opacity="0"/>
                </linearGradient>
                <linearGradient id="humanGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.2"/>
                    <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                </linearGradient>
            </defs>

            <!-- Grid Lines -->
            <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#f1f5f9" stroke-width="1" />
            <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" stroke="#f1f5f9" stroke-width="1" />
            <line x1="${padding}" y1="${(height - padding + padding) / 2}" x2="${width - padding}" y2="${(height - padding + padding) / 2}" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="2" />

            <!-- Human Data -->
            ${humanPath ? `<path d="${getAreaPath(humanPath, humanData)}" fill="url(#humanGradient)" />` : ''}
            ${humanPath ? `<path d="${humanPath}" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" />` : ''}
            
            <!-- Bot Data -->
            <path d="${getAreaPath(botPath, botData)}" fill="url(#botGradient)" />
            <path d="${botPath}" fill="none" stroke="#4f46e5" stroke-width="3" stroke-linecap="round" />
            
            <!-- Labels -->
            ${labels.map((lbl, i) => {
		const x = padding + (i * stepX);
		if (i % 2 !== 0) return ''; // Skip half
		return `<text x="${x}" y="${height - 20}" font-size="11" fill="#94a3b8" text-anchor="middle" font-weight="500">${lbl}</text>`;
	}).join('')}
        </svg>
    `;

	container.innerHTML = svg;
}

document.addEventListener('DOMContentLoaded', function () {
	if (!window.wpciData) return;
	const ctx = document.getElementById('wpciBotChart');
	if (ctx) {
		// Debounce resize
		let resizeTimer;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(() => {
				renderSimpleChart(ctx, wpciData.labels, wpciData.botHits, wpciData.humanHits || []);
			}, 250);
		});

		renderSimpleChart(ctx, wpciData.labels, wpciData.botHits, wpciData.humanHits || []);
	}
});
