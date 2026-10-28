import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    DoughnutController,
    ArcElement,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    DoughnutController,
    ArcElement,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
    Legend,
);

Chart.defaults.font.family = "'Cairo', sans-serif";
Chart.defaults.font.size = 12;

function cssColor(variable, fallback) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();

    return value || fallback;
}

function palette() {
    const isDark = document.documentElement.classList.contains('dark');

    return {
        text: isDark ? '#d6d3d1' : '#44403c',
        muted: isDark ? '#a8a29e' : '#78716c',
        grid: isDark ? 'rgba(214, 211, 209, 0.08)' : 'rgba(68, 64, 60, 0.08)',
        brand: cssColor('--color-brand-400', '#facc15'),
        maroon: cssColor('--color-maroon-500', '#a83232'),
        colors: [
            cssColor('--color-brand-400', '#facc15'),
            cssColor('--color-brand-600', '#ca8a04'),
            cssColor('--color-maroon-500', '#a83232'),
            '#0f766e',
            '#0284c7',
            '#7c3aed',
            '#ea580c',
            '#e11d48',
        ],
    };
}

function baseOptions(type) {
    const colors = palette();
    const circular = type === 'doughnut';

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 500 },
        plugins: {
            legend: {
                display: circular,
                position: 'bottom',
                rtl: true,
                labels: {
                    color: colors.text,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 18,
                    font: { family: "'Cairo', sans-serif", size: 12 },
                },
            },
            tooltip: {
                rtl: true,
                backgroundColor: '#1c1a16',
                titleFont: { family: "'Cairo', sans-serif" },
                bodyFont: { family: "'Cairo', sans-serif" },
                padding: 12,
                cornerRadius: 12,
                displayColors: true,
            },
        },
        scales: circular
            ? {}
            : {
                  x: {
                      grid: { display: false },
                      ticks: {
                          color: colors.muted,
                          maxRotation: 0,
                          autoSkip: true,
                          maxTicksLimit: 6,
                          font: { family: "'Cairo', sans-serif" },
                      },
                  },
                  y: {
                      beginAtZero: true,
                      grid: { color: colors.grid },
                      ticks: { color: colors.muted, precision: 0, font: { family: "'Cairo', sans-serif" } },
                  },
              },
    };
}

export function chartConfig(type, payload) {
    const colors = palette();

    if (type === 'doughnut') {
        return {
            type,
            data: {
                labels: payload.labels,
                datasets: [
                    {
                        data: payload.values,
                        backgroundColor: colors.colors,
                        borderColor: document.documentElement.classList.contains('dark')
                            ? cssColor('--color-night-900', '#151310')
                            : '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                ...baseOptions(type),
                cutout: '68%',
            },
        };
    }

    return {
        type: 'bar',
        data: {
            labels: payload.labels,
            datasets: (payload.datasets || []).map((dataset, index) => ({
                label: dataset.label,
                data: dataset.values,
                backgroundColor: index === 0 ? colors.brand : colors.maroon,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 26,
            })),
        },
        options: baseOptions(type),
    };
}

export { Chart };
