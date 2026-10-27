function currentTheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function refreshCharts() {
    if (!window.Chart) {
        return;
    }

    const colors = window.chartColors ? window.chartColors() : { tick: '#64748b', grid: '#f1f5f9' };
    const charts = window.__charts || Object.values(Chart.instances || {});

    Chart.defaults.color = colors.tick;

    charts.forEach((chart) => {
        if (chart.options.scales) {
            Object.entries(chart.options.scales).forEach(([axis, scale]) => {
                if (!scale) {
                    return;
                }

                if (scale.ticks) {
                    scale.ticks.color = colors.tick;
                }

                if (scale.grid) {
                    scale.grid.color = axis === 'y' ? colors.grid : 'transparent';
                }

                if (scale.border) {
                    scale.border.color = 'transparent';
                }
            });
        }

        if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
            chart.options.plugins.legend.labels.color = colors.tick;
        }

        chart.update('none');
    });
}

function applyTheme(theme) {
    const isDark = theme === 'dark';

    document.documentElement.classList.toggle('dark', isDark);

    try {
        localStorage.setItem('theme', theme);
    } catch (error) {
        // localStorage boleh tidak tersedia (mode privat); tema tetap berlaku di sesi ini.
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
        button.setAttribute('title', isDark ? 'Mode terang' : 'Mode gelap');
    });

    refreshCharts();
}

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
    });
});

applyTheme(currentTheme());

window.addEventListener('load', refreshCharts);
