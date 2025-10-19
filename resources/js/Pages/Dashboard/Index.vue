<script setup lang="ts">
import { computed, ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import Message from 'primevue/message';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const logoImg = '/images/logo.png'; 


// Filtros específicos para cada gráfico
const selectedMunicipios = ref<string[]>([]);
const selectedEspecies = ref<string[]>([]);
const selectedCulturas = ref<string[]>([]);
const selectedPeriod = ref('12m');

// Opções de período
const periodOptions = [
    { label: 'Último mês', value: '1m' },
    { label: 'Últimos 3 meses', value: '3m' },
    { label: 'Últimos 6 meses', value: '6m' },
    { label: 'Últimos 12 meses', value: '12m' },
    { label: 'Este ano', value: 'year' }
];

// Função para exportar dados
const exportData = (format: 'csv' | 'excel' | 'pdf', chartName: string) => {
    console.log(`Exportando ${chartName} em formato: ${format}`);
    
    // Determinar quais dados exportar com base no nome do gráfico
    let data: any[] = [];
    let filename = '';
    let headers: string[] = [];
    
    switch (chartName) {
        case 'propriedades-municipio':
            data = produtoresFiltrados.value;
            filename = 'propriedades_por_municipio';
            headers = ['Município', 'Total'];
            break;
        case 'animais-especie':
            data = animaisFiltrados.value;
            filename = 'animais_por_especie';
            headers = ['Espécie', 'Total de Animais'];
            break;
        case 'hectares-cultura':
            data = culturasFiltradas.value;
            filename = 'hectares_por_cultura';
            headers = ['Cultura', 'Total de Hectares'];
            break;
        case 'evolucao-cadastros':
            data = cadastrosFiltrados.value;
            filename = 'evolucao_cadastros';
            headers = ['Mês', 'Produtores', 'Propriedades'];
            break;
        default:
            console.error('Tipo de gráfico desconhecido:', chartName);
            return;
    }
    
    if (format === 'csv') {
        exportToCSV(data, filename, headers, chartName);
    } else if (format === 'excel') {
        // Redirecionar para a rota de exportação Excel
        window.location.href = '/exportar/propriedades/excel';
    } else if (format === 'pdf') {
        console.log('Exportação PDF em desenvolvimento');
        toast.add({
            severity: 'info',
            summary: 'Em desenvolvimento',
            detail: 'Exportação para PDF em breve',
            life: 3000,
        });
    }
};

// Função auxiliar para exportar para CSV
const exportToCSV = (data: any[], filename: string, headers: string[], chartName: string) => {
    try {
        // Verificar se há dados
        if (!data || data.length === 0) {
            toast.add({
                severity: 'warn',
                summary: 'Aviso',
                detail: 'Não há dados para exportar',
                life: 3000,
            });
            return;
        }
        
        // BOM UTF-8 para Excel reconhecer corretamente a codificação
        const BOM = '\uFEFF';
        
        // Preparar linhas do CSV (usando ponto e vírgula como separador - padrão brasileiro)
        let csvContent = BOM;
        
        // Adicionar cabeçalho
        csvContent += headers.join(';') + '\n';
        
        // Adicionar dados
        data.forEach(item => {
            let row: string[] = [];
            
            switch (chartName) {
                case 'propriedades-municipio':
                    row = [
                        `"${(item.municipio || '').replace(/"/g, '""')}"`,
                        (item.total || 0).toString()
                    ];
                    break;
                case 'animais-especie':
                    row = [
                        `"${(item.especie || '').replace(/"/g, '""')}"`,
                        (item.total || 0).toString()
                    ];
                    break;
                case 'hectares-cultura':
                    row = [
                        `"${(item.cultura || '').replace(/"/g, '""')}"`,
                        (item.hectares || 0).toFixed(2).replace('.', ',')
                    ];
                    break;
                case 'evolucao-cadastros':
                    row = [
                        `"${(item.mes || '').replace(/"/g, '""')}"`,
                        (item.produtores || 0).toString(),
                        (item.propriedades || 0).toString()
                    ];
                    break;
            }
            
            csvContent += row.join(';') + '\n';
        });
        
        // Criar blob e baixar com encoding UTF-8
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Limpar URL do objeto
        URL.revokeObjectURL(url);
        
        toast.add({
            severity: 'success',
            summary: 'Exportação',
            detail: 'Arquivo CSV baixado com sucesso',
            life: 3000,
        });
    } catch (error) {
        console.error('Erro ao exportar CSV:', error);
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Erro ao exportar dados',
            life: 3000,
        });
    }
};

// Função para recarregar página
const reloadPage = () => {
    window.location.reload();
};

// Dados filtrados em tempo real - Municípios
const produtoresFiltrados = computed(() => {
    if (selectedMunicipios.value.length === 0) {
        return props.produtoresPorMunicipio;
    }
    return props.produtoresPorMunicipio.filter(item => 
        selectedMunicipios.value.includes(item.municipio)
    );
});

// Dados filtrados em tempo real - Espécies
const animaisFiltrados = computed(() => {
    if (selectedEspecies.value.length === 0) {
        return props.animaisPorEspecie;
    }
    return props.animaisPorEspecie.filter(item => 
        selectedEspecies.value.includes(item.especie)
    );
});

// Dados filtrados em tempo real - Culturas
const culturasFiltradas = computed(() => {
    if (selectedCulturas.value.length === 0) {
        return props.hectaresPorCultura;
    }
    return props.hectaresPorCultura.filter(item => 
        selectedCulturas.value.includes(item.cultura)
    );
});

// Dados filtrados em tempo real - Cadastros por Período
const cadastrosFiltrados = computed(() => {
    const meses = props.cadastrosPorMes;
    
    if (selectedPeriod.value === '12m') {
        return meses;
    }
    
    const periodMap: { [key: string]: number } = {
        '1m': 1,
        '3m': 3,
        '6m': 6,
        'year': 12
    };
    
    const qtdMeses = periodMap[selectedPeriod.value] || 12;
    return meses.slice(-qtdMeses);
});

// Props do Inertia
interface Props {
    produtoresPorMunicipio: Array<{ municipio: string; total: number }>;
    animaisPorEspecie: Array<{ especie: string; total: number }>;
    cadastrosPorMes: Array<{ mes: string; produtores: number; propriedades: number }>;
    hectaresPorCultura: Array<{ cultura: string; hectares: number }>;
    stats: {
        total_produtores: number;
        total_propriedades: number;
        total_unidades: number;
        total_animais: number;
        total_hectares: number;
    };
}

const props = defineProps<Props>();

// Paleta de cores PrimeVue
const colors = {
    primary: '#3B82F6',
    success: '#10B981',
    warning: '#F59E0B',
    danger: '#EF4444',
    info: '#06B6D4',
    purple: '#8B5CF6',
    pink: '#EC4899',
    orange: '#F97316'
};

// ==================== GRÁFICO 1: BARRAS - Propriedades por Município ====================
const chartBarras = computed(() => ({
    series: [{
        name: 'Propriedades',
        data: produtoresFiltrados.value.map(item => item.total)
    }],
    chartOptions: {
        chart: {
            type: 'bar' as const,
            height: '100%',
            background: 'transparent',
            foreColor: '#cbd5e1',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                },
                export: {
                    csv: {
                        filename: 'propriedades-por-municipio',
                        headerCategory: 'Município',
                        headerValue: 'Total'
                    },
                    svg: {
                        filename: 'propriedades-por-municipio',
                    },
                    png: {
                        filename: 'propriedades-por-municipio',
                    }
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            zoom: {
                enabled: true,
                type: 'x' as const,
                autoScaleYaxis: true
            },
            events: {
                dataPointSelection: (event: any, chartContext: any, config: any) => {
                    const municipio = produtoresFiltrados.value[config.dataPointIndex].municipio;
                    console.log('Município selecionado:', municipio);
                    // Drill-down - pode navegar para detalhes do município
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 8,
                borderRadiusApplication: 'end' as const,
                borderRadiusWhenStacked: 'last' as const,
                columnWidth: '70%',
                dataLabels: {
                    position: 'top'
                },
                distributed: false
            }
        },
        colors: [colors.primary],
        dataLabels: {
            enabled: true,
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ['#304758'],
                fontWeight: 600
            },
            background: {
                enabled: true,
                foreColor: '#fff',
                padding: 4,
                borderRadius: 4,
                borderWidth: 1,
                borderColor: colors.primary,
                opacity: 0.9
            }
        },
        xaxis: {
            categories: produtoresFiltrados.value.map(item => item.municipio),
            labels: {
                rotate: -45,
                rotateAlways: false,
                hideOverlappingLabels: true,
                style: {
                    fontSize: '11px',
                    fontWeight: 500
                },
                trim: true
            },
            axisBorder: {
                show: true,
                color: '#e0e0e0'
            },
            axisTicks: {
                show: true,
                color: '#e0e0e0'
            },
            tooltip: {
                enabled: true
            }
        },
        yaxis: {
            title: {
                text: 'Quantidade de Propriedades',
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                    color: '#666'
                }
            },
            labels: {
                formatter: (val: number) => Math.floor(val).toString(),
                style: {
                    fontSize: '11px'
                }
            },
            min: 0,
            forceNiceScale: true
        },
        grid: {
            show: true,
            borderColor: '#f1f1f1',
            strokeDashArray: 4,
            position: 'back' as const,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 10,
                bottom: 0,
                left: 10
            }
        },
        tooltip: {
            enabled: true,
            shared: false,
            intersect: true,
            followCursor: true,
            theme: 'dark' as const,
            style: {
                fontSize: '13px'
            },
            y: {
                formatter: (val: number) => `${val} propriedade${val !== 1 ? 's' : ''}`,
                title: {
                    formatter: () => 'Total:'
                }
            },
            marker: {
                show: true
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'lighten' as const,
                    value: 0.1
                }
            },
            active: {
                allowMultipleDataPointsSelection: true,
                filter: {
                    type: 'darken' as const,
                    value: 0.3
                }
            }
        },
        theme: {
            mode: 'dark' as const,
            palette: 'palette1'
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: '100%'
                },
                xaxis: {
                    labels: {
                        rotate: -90,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '80%'
                    }
                }
            }
        }]
    } as ApexOptions
}));

// ==================== GRÁFICO 2: PIZZA - Animais por Espécie ====================
const chartPizza = computed(() => ({
    series: animaisFiltrados.value.map(item => item.total),
    chartOptions: {
        chart: {
            type: 'pie' as const,
            height: '100%',
            background: 'transparent',
            foreColor: '#cbd5e1',
            toolbar: {
                show: true,
                tools: {
                    download: true
                },
                export: {
                    csv: {
                        filename: 'animais-por-especie',
                        headerCategory: 'Espécie',
                        headerValue: 'Total'
                    },
                    svg: {
                        filename: 'animais-por-especie',
                    },
                    png: {
                        filename: 'animais-por-especie',
                    }
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            events: {
                legendClick: (_chartContext: any, seriesIndex: number, _config: any) => {
                    console.log('Espécie selecionada:', animaisFiltrados.value[seriesIndex].especie);
                },
                dataPointSelection: (_event: any, _chartContext: any, config: any) => {
                    const especie = animaisFiltrados.value[config.dataPointIndex].especie;
                    console.log('Drill-down para espécie:', especie);
                }
            }
        },
        labels: animaisFiltrados.value.map(item => item.especie),
        colors: [colors.primary, colors.success, colors.warning, colors.danger, colors.info, colors.purple, colors.pink, colors.orange],
        dataLabels: {
            enabled: true,
            formatter: (val: number, opts: any) => {
                const total = animaisFiltrados.value[opts.seriesIndex].total;
                return `${val.toFixed(1)}%\n(${total.toLocaleString()})`;
            },
            style: {
                fontSize: '13px',
                fontWeight: 600,
                colors: ['#fff']
            },
            background: {
                enabled: true,
                foreColor: '#000',
                opacity: 0.7,
                borderRadius: 4,
                borderWidth: 1,
                borderColor: '#fff'
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 1,
                blur: 1,
                opacity: 0.45
            }
        },
        legend: {
            position: 'bottom' as const,
            fontSize: '13px',
            fontWeight: 500,
            onItemClick: {
                toggleDataSeries: true
            },
            onItemHover: {
                highlightDataSeries: true
            },
            markers: {
                width: 14,
                height: 14,
                radius: 12,
                strokeWidth: 0
            },
            itemMargin: {
                horizontal: 8,
                vertical: 5
            },
            formatter: (seriesName: string, opts: any) => {
                const total = opts.w.globals.series[opts.seriesIndex];
                return `${seriesName}: ${total.toLocaleString()}`;
            }
        },
        plotOptions: {
            pie: {
                expandOnClick: true,
                donut: {
                    size: '0%'
                },
                customScale: 1,
                offsetX: 0,
                offsetY: 0,
                dataLabels: {
                    offset: 0,
                    minAngleToShowLabel: 10
                }
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'lighten' as const,
                    value: 0.15
                }
            },
            active: {
                allowMultipleDataPointsSelection: true,
                filter: {
                    type: 'darken' as const,
                    value: 0.15
                }
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['#fff']
        },
        tooltip: {
            enabled: true,
            theme: 'dark' as const,
            style: {
                fontSize: '13px'
            },
            y: {
                formatter: (val: number) => `${val.toLocaleString()} animais`,
                title: {
                    formatter: (seriesName: string) => `${seriesName}:`
                }
            },
            fillSeriesColor: false
        },
        theme: {
            mode: 'dark' as const,
            palette: 'palette1'
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: '100%'
                },
                legend: {
                    position: 'bottom' as const,
                    fontSize: '12px'
                },
                dataLabels: {
                    style: {
                        fontSize: '11px'
                    }
                }
            }
        }]
    } as ApexOptions
}));

// ==================== GRÁFICO 3: LINHA - Evolução de Cadastros ====================
const chartLinha = computed(() => ({
    series: [
        {
            name: 'Produtores',
            data: cadastrosFiltrados.value.map(item => item.produtores)
        },
        {
            name: 'Propriedades',
            data: cadastrosFiltrados.value.map(item => item.propriedades)
        }
    ],
    chartOptions: {
        chart: {
            type: 'line' as const,
            height: '100%',
            background: 'transparent',
            foreColor: '#cbd5e1',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                },
                export: {
                    csv: {
                        filename: 'evolucao-cadastros',
                        columnDelimiter: ',',
                        headerCategory: 'Mês',
                        headerValue: 'value'
                    },
                    svg: {
                        filename: 'evolucao-cadastros',
                    },
                    png: {
                        filename: 'evolucao-cadastros',
                    }
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            zoom: {
                enabled: true,
                type: 'xy' as const,
                autoScaleYaxis: true,
                zoomedArea: {
                    fill: {
                        color: colors.primary,
                        opacity: 0.1
                    },
                    stroke: {
                        color: colors.primary,
                        opacity: 0.4,
                        width: 1
                    }
                }
            },
            selection: {
                enabled: true,
                type: 'xy' as const,
                fill: {
                    color: colors.success,
                    opacity: 0.1
                },
                stroke: {
                    width: 1,
                    dashArray: 3,
                    color: colors.success,
                    opacity: 0.4
                }
            }
        },
        colors: [colors.primary, colors.success],
        stroke: {
            width: [3, 3],
            curve: 'smooth' as const,
            dashArray: [0, 0]
        },
        markers: {
            size: [5, 5],
            colors: [colors.primary, colors.success],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: {
                size: 7,
                sizeOffset: 3
            },
            discrete: []
        },
        xaxis: {
            categories: cadastrosFiltrados.value.map(item => item.mes),
            labels: {
                style: {
                    fontSize: '11px',
                    fontWeight: 500
                },
                rotate: 0,
                rotateAlways: false,
                hideOverlappingLabels: true
            },
            axisBorder: {
                show: true,
                color: '#e0e0e0'
            },
            axisTicks: {
                show: true,
                color: '#e0e0e0'
            },
            tooltip: {
                enabled: true
            },
            crosshairs: {
                show: true,
                width: 1,
                stroke: {
                    color: '#b6b6b6',
                    width: 1,
                    dashArray: 3
                }
            }
        },
        yaxis: {
            title: {
                text: 'Quantidade de Cadastros',
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                    color: '#666'
                }
            },
            labels: {
                formatter: (val: number) => Math.floor(val).toString(),
                style: {
                    fontSize: '11px'
                }
            },
            min: 0,
            forceNiceScale: true
        },
        grid: {
            show: true,
            borderColor: '#f1f1f1',
            strokeDashArray: 4,
            position: 'back' as const,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 10,
                bottom: 0,
                left: 10
            }
        },
        legend: {
            position: 'bottom' as const,
            horizontalAlign: 'center' as const,
            fontSize: '13px',
            fontWeight: 500,
            offsetY: 5,
            onItemClick: {
                toggleDataSeries: true
            },
            onItemHover: {
                highlightDataSeries: true
            },
            markers: {
                width: 12,
                height: 12,
                radius: 2,
                strokeWidth: 0
            },
            itemMargin: {
                horizontal: 10,
                vertical: 5
            }
        },
        tooltip: {
            enabled: true,
            shared: true,
            intersect: false,
            followCursor: true,
            theme: 'dark' as const,
            style: {
                fontSize: '13px'
            },
            x: {
                show: true,
                format: 'MMM/yyyy'
            },
            y: {
                formatter: (val: number) => `${val} cadastro${val !== 1 ? 's' : ''}`,
                title: {
                    formatter: (seriesName: string) => `${seriesName}:`
                }
            },
            marker: {
                show: true
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'lighten' as const,
                    value: 0.1
                }
            },
            active: {
                allowMultipleDataPointsSelection: true,
                filter: {
                    type: 'darken' as const,
                    value: 0.2
                }
            }
        },
        fill: {
            opacity: 1,
            type: 'solid'
        },
        theme: {
            mode: 'dark' as const,
            palette: 'palette1'
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: '100%'
                },
                xaxis: {
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                legend: {
                    position: 'bottom' as const,
                    fontSize: '12px'
                },
                stroke: {
                    width: [2, 2]
                },
                markers: {
                    size: [4, 4]
                }
            }
        }]
    } as ApexOptions
}));

// ==================== GRÁFICO 4: ÁREA - Hectares por Cultura ====================
const chartArea = computed(() => ({
    series: [{
        name: 'Hectares',
        data: culturasFiltradas.value.map(item => item.hectares)
    }],
    chartOptions: {
        chart: {
            type: 'area' as const,
            height: '100%',
            background: 'transparent',
            foreColor: '#cbd5e1',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                },
                export: {
                    csv: {
                        filename: 'hectares-por-cultura',
                        headerCategory: 'Cultura',
                        headerValue: 'Hectares'
                    },
                    svg: {
                        filename: 'hectares-por-cultura',
                    },
                    png: {
                        filename: 'hectares-por-cultura',
                    }
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            zoom: {
                enabled: true,
                type: 'x' as const,
                autoScaleYaxis: true
            },
            events: {
                dataPointSelection: (_event: any, _chartContext: any, config: any) => {
                    const cultura = culturasFiltradas.value[config.dataPointIndex].cultura;
                    console.log('Cultura selecionada:', cultura);
                }
            }
        },
        colors: [colors.success],
        fill: {
            type: 'gradient' as const,
            gradient: {
                shade: 'light' as const,
                type: 'vertical' as const,
                shadeIntensity: 0.5,
                gradientToColors: [colors.info],
                inverseColors: false,
                opacityFrom: 0.8,
                opacityTo: 0.2,
                stops: [0, 50, 100],
                colorStops: []
            }
        },
        dataLabels: {
            enabled: true,
            offsetY: -5,
            style: {
                fontSize: '11px',
                fontWeight: 600,
                colors: [colors.success]
            },
            background: {
                enabled: true,
                foreColor: '#fff',
                padding: 4,
                borderRadius: 4,
                borderWidth: 1,
                borderColor: colors.success,
                opacity: 0.9
            },
            formatter: (val: number) => val.toFixed(1) + ' ha'
        },
        stroke: {
            curve: 'smooth' as const,
            width: 2,
            colors: [colors.success]
        },
        markers: {
            size: 4,
            colors: [colors.success],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: {
                size: 6,
                sizeOffset: 2
            }
        },
        xaxis: {
            categories: culturasFiltradas.value.map(item => item.cultura),
            labels: {
                style: {
                    fontSize: '11px',
                    fontWeight: 500
                },
                rotate: 0,
                rotateAlways: false,
                hideOverlappingLabels: true,
                trim: true
            },
            axisBorder: {
                show: true,
                color: '#e0e0e0'
            },
            axisTicks: {
                show: true,
                color: '#e0e0e0'
            },
            tooltip: {
                enabled: true
            }
        },
        yaxis: {
            title: {
                text: 'Hectares',
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                    color: '#666'
                }
            },
            labels: {
                formatter: (val: number) => val.toFixed(1),
                style: {
                    fontSize: '11px'
                }
            },
            min: 0,
            forceNiceScale: true
        },
        grid: {
            show: true,
            borderColor: '#f1f1f1',
            strokeDashArray: 4,
            position: 'back' as const,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 10,
                bottom: 0,
                left: 10
            }
        },
        tooltip: {
            enabled: true,
            shared: false,
            intersect: true,
            followCursor: true,
            theme: 'dark' as const,
            style: {
                fontSize: '13px'
            },
            x: {
                show: true
            },
            y: {
                formatter: (val: number) => `${val.toFixed(2)} ha`,
                title: {
                    formatter: () => 'Área cultivada:'
                }
            },
            marker: {
                show: true
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'lighten' as const,
                    value: 0.1
                }
            },
            active: {
                allowMultipleDataPointsSelection: true,
                filter: {
                    type: 'darken' as const,
                    value: 0.2
                }
            }
        },
        theme: {
            mode: 'dark' as const,
            palette: 'palette1'
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: '100%'
                },
                xaxis: {
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 3
                }
            }
        }]
    } as ApexOptions
}));

// Verificar se há dados
const temDados = computed(() => 
    props.produtoresPorMunicipio.length > 0 ||
    props.animaisPorEspecie.length > 0 ||
    props.hectaresPorCultura.length > 0
);
</script>

<template>
    <MainLayout title="Dashboard">
        <div class="p-4">
            <!-- Toolbar com título e ações -->
            <Toolbar class="mb-4">
                <template #start>
                    <div class="flex align-items-center gap-3">
                        <img :src="logoImg" alt="Agropesca Jacaré" style="width: 48px; height: 48px; object-fit: contain;" />
                        <div>
                            <h1 class="text-2xl font-bold text-900">
                                Dashboard
                            </h1>
                            <p class="text-sm text-600 mt-1">Visão geral do sistema agropecuário</p>
                        </div>
                    </div>
                </template>
                
                <template #end>
                    <Button 
                        label="Atualizar" 
                        icon="pi pi-refresh" 
                        severity="secondary"
                        outlined
                        @click="reloadPage"
                    />
                </template>
            </Toolbar>

            <Divider />

            <!-- Cards de Estatísticas Gerais -->
            <div class="grid mb-5">
                <div class="col-12 sm:col-6 lg:col-4 xl:col">
                    <Card class="hover:shadow-5 transition-all transition-duration-300 cursor-default">
                        <template #content>
                            <div class="flex align-items-center gap-3 p-2">
                                <div class="bg-blue-100 border-round-lg flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                                    <i class="pi pi-users text-blue-600 text-3xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-3xl font-bold text-900 line-height-1">{{ stats.total_produtores }}</div>
                                    <div class="text-sm text-600 mt-1 font-medium">Produtores Rurais</div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="col-12 sm:col-6 lg:col-4 xl:col">
                    <Card class="hover:shadow-5 transition-all transition-duration-300 cursor-default">
                        <template #content>
                            <div class="flex align-items-center gap-3 p-2">
                                <div class="bg-green-100 border-round-lg flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                                    <i class="pi pi-home text-green-600 text-3xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-3xl font-bold text-900 line-height-1">{{ stats.total_propriedades }}</div>
                                    <div class="text-sm text-600 mt-1 font-medium">Propriedades</div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="col-12 sm:col-6 lg:col-4 xl:col">
                    <Card class="hover:shadow-5 transition-all transition-duration-300 cursor-default">
                        <template #content>
                            <div class="flex align-items-center gap-3 p-2">
                                <div class="bg-yellow-100 border-round-lg flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                                    <i class="pi pi-flag text-yellow-600 text-3xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-3xl font-bold text-900 line-height-1">{{ stats.total_unidades }}</div>
                                    <div class="text-sm text-600 mt-1 font-medium">Unidades de Produção</div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="col-12 sm:col-6 lg:col-4 xl:col">
                    <Card class="hover:shadow-5 transition-all transition-duration-300 cursor-default">
                    <template #content>
                        <div class="flex align-items-center gap-3 p-2">
                            <div class="bg-purple-100 border-round-lg flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                                <i class="pi pi-verified text-purple-600 text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900 line-height-1">{{ stats.total_animais.toLocaleString('pt-BR') }}</div>
                                <div class="text-sm text-600 mt-1 font-medium">Animais</div>
                            </div>
                        </div>
                    </template>
                </Card>
                </div>

                <div class="col-12 sm:col-6 lg:col-4 xl:col">
                    <Card class="hover:shadow-5 transition-all transition-duration-300 cursor-default">
                    <template #content>
                        <div class="flex align-items-center gap-3 p-2">
                            <div class="bg-orange-100 border-round-lg flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                                <i class="pi pi-chart-line text-orange-600 text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900 line-height-1">{{ stats.total_hectares.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                                <div class="text-sm text-600 mt-1 font-medium">Hectares Cultivados</div>
                            </div>
                        </div>
                    </template>
                </Card>
                </div>
            </div>

            <!-- Mensagem se não houver dados -->
            <Message v-if="!temDados" severity="info" :closable="false" class="mb-6">
                Nenhum dado disponível para exibir gráficos. Cadastre produtores, propriedades e unidades para visualizar estatísticas.
            </Message>

            <!-- Grid de Gráficos Responsivo -->
            <div v-else class="grid">
                <!-- Gráfico 1: Barras - Propriedades por Município -->
                <div class="col-12">
                <Card v-if="produtoresPorMunicipio.length > 0" class="shadow-3 hover:shadow-6 transition-all transition-duration-300 h-full flex flex-column">
                    <template #title>
                        <div class="flex align-items-center justify-content-between w-full">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-chart-bar text-blue-500"></i>
                                <span>Propriedades por Município</span>
                            </div>
                            <Button 
                                icon="pi pi-download" 
                                severity="secondary" 
                                text 
                                rounded 
                                @click="() => exportData('csv', 'propriedades-municipio')"
                                v-tooltip.left="'Exportar dados'"
                            />
                        </div>
                    </template>
                    <template #subtitle>
                        <div class="flex flex-column gap-3 mt-2 p-3 surface-ground border-round-lg border-1 surface-border">
                            <div class="flex align-items-center gap-3 flex-wrap">
                                <label class="text-sm font-semibold text-700 flex align-items-center white-space-nowrap">
                                    <i class="pi pi-filter mr-2"></i>
                                    Filtrar municípios:
                                </label>
                                <MultiSelect 
                                    v-model="selectedMunicipios" 
                                    :options="produtoresPorMunicipio.map(m => m.municipio)" 
                                    placeholder="Todos os municípios"
                                    :maxSelectedLabels="3"
                                    class="flex-1"
                                    style="min-width: 200px;"
                                    display="chip"
                                    :showClear="true"
                                />
                            </div>
                            <span class="text-xs text-600 mt-2">
                                <i class="pi pi-info-circle"></i> Filtragem em tempo real • Use zoom e clique nas barras
                            </span>
                        </div>
                    </template>
                    <template #content>
                        <div class="w-full" style="height: 450px; min-height: 400px;">
                            <VueApexCharts
                                type="bar"
                                height="100%"
                                :options="chartBarras.chartOptions"
                                :series="chartBarras.series"
                            />
                        </div>
                    </template>
                </Card>
                </div>

                <!-- Gráfico 2: Pizza - Animais por Espécie -->
                <div class="col-12 lg:col-6">
                <Card v-if="animaisPorEspecie.length > 0" class="shadow-3 hover:shadow-6 transition-all transition-duration-300 h-full flex flex-column">
                    <template #title>
                        <div class="flex align-items-center justify-content-between w-full">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-chart-pie text-green-500"></i>
                                <span>Distribuição de Animais</span>
                            </div>
                            <Button 
                                icon="pi pi-download" 
                                severity="secondary" 
                                text 
                                rounded 
                                @click="() => exportData('csv', 'animais-especie')"
                                v-tooltip.left="'Exportar dados'"
                            />
                        </div>
                    </template>
                    <template #subtitle>
                        <div class="flex flex-column gap-3 mt-2 p-3 surface-ground border-round-lg border-1 surface-border">
                            <div class="flex align-items-center gap-3 flex-wrap">
                                <label class="text-sm font-semibold text-700 flex align-items-center white-space-nowrap">
                                    <i class="pi pi-filter mr-2"></i>
                                    Filtrar espécies:
                                </label>
                                <MultiSelect 
                                    v-model="selectedEspecies" 
                                    :options="animaisPorEspecie.map(e => e.especie)" 
                                    placeholder="Todas as espécies"
                                    :maxSelectedLabels="3"
                                    class="flex-1"
                                    style="min-width: 200px;"
                                    display="chip"
                                    :showClear="true"
                                />
                            </div>
                            <span class="text-xs text-600 mt-2">
                                <i class="pi pi-info-circle"></i> Filtragem em tempo real • Clique nas legendas para comparar
                            </span>
                        </div>
                    </template>
                    <template #content>
                        <div class="w-full" style="height: 450px; min-height: 400px;">
                            <VueApexCharts
                                type="pie"
                                height="100%"
                                :options="chartPizza.chartOptions"
                                :series="chartPizza.series"
                            />
                        </div>
                    </template>
                </Card>
                </div>

                <!-- Gráfico 3: Linha - Evolução de Cadastros -->
                <div class="col-12 lg:col-6">
                <Card class="shadow-3 hover:shadow-6 transition-all transition-duration-300 h-full flex flex-column">
                    <template #title>
                        <div class="flex align-items-center justify-content-between w-full">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-chart-line text-purple-500"></i>
                                <span>Evolução de Cadastros</span>
                            </div>
                            <Button 
                                icon="pi pi-download" 
                                severity="secondary" 
                                text 
                                rounded 
                                @click="() => exportData('csv', 'evolucao-cadastros')"
                                v-tooltip.left="'Exportar dados'"
                            />
                        </div>
                    </template>
                    <template #subtitle>
                        <div class="flex flex-column gap-3 mt-2 p-3 surface-ground border-round-lg border-1 surface-border">
                            <div class="flex align-items-center gap-3 flex-wrap">
                                <label class="text-sm font-semibold text-700 flex align-items-center white-space-nowrap">
                                    <i class="pi pi-calendar mr-2"></i>
                                    Período:
                                </label>
                                <Dropdown 
                                    v-model="selectedPeriod" 
                                    :options="periodOptions" 
                                    optionLabel="label" 
                                    optionValue="value"
                                    placeholder="Selecione o período"
                                    class="flex-1"
                                    style="min-width: 200px;"
                                />
                            </div>
                            <span class="text-xs text-600 mt-2">
                                <i class="pi pi-info-circle"></i> Filtragem em tempo real • Use zoom XY e seleção de área
                            </span>
                        </div>
                    </template>
                    <template #content>
                        <div class="w-full" style="height: 450px; min-height: 400px;">
                            <VueApexCharts
                                type="line"
                                height="100%"
                                :options="chartLinha.chartOptions"
                                :series="chartLinha.series"
                            />
                        </div>
                    </template>
                </Card>
                </div>

                <!-- Gráfico 4: Área - Hectares por Cultura -->
                <div class="col-12">
                <Card v-if="hectaresPorCultura.length > 0" class="shadow-3 hover:shadow-6 transition-all transition-duration-300 h-full flex flex-column">
                    <template #title>
                        <div class="flex align-items-center justify-content-between w-full">
                            <div class="flex align-items-center gap-2">
                                <i class="pi pi-sun text-orange-500"></i>
                                <span>Hectares por Cultura</span>
                            </div>
                            <Button 
                                icon="pi pi-download" 
                                severity="secondary" 
                                text 
                                rounded 
                                @click="() => exportData('csv', 'hectares-cultura')"
                                v-tooltip.left="'Exportar dados'"
                            />
                        </div>
                    </template>
                    <template #subtitle>
                        <div class="flex flex-column gap-3 mt-2 p-3 surface-ground border-round-lg border-1 surface-border">
                            <div class="flex align-items-center gap-3 flex-wrap">
                                <label class="text-sm font-semibold text-700 flex align-items-center white-space-nowrap">
                                    <i class="pi pi-filter mr-2"></i>
                                    Filtrar culturas:
                                </label>
                                <MultiSelect 
                                    v-model="selectedCulturas" 
                                    :options="hectaresPorCultura.map(c => c.cultura)" 
                                    placeholder="Todas as culturas"
                                    :maxSelectedLabels="3"
                                    class="flex-1"
                                    style="min-width: 200px;"
                                    display="chip"
                                    :showClear="true"
                                />
                            </div>
                            <span class="text-xs text-600 mt-2">
                                <i class="pi pi-info-circle"></i> Filtragem em tempo real • Gradiente visual e zoom habilitado
                            </span>
                        </div>
                    </template>
                    <template #content>
                        <div class="w-full" style="height: 450px; min-height: 400px;">
                            <VueApexCharts
                                type="area"
                                height="100%"
                                :options="chartArea.chartOptions"
                                :series="chartArea.series"
                            />
                        </div>
                    </template>
                </Card>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Garantir que os componentes PrimeVue Card sejam flexíveis */
:deep(.p-card) {
    height: 100%;
    display: flex;
    flex-direction: column;
}

:deep(.p-card-body) {
    flex: 1;
    display: flex;
    flex-direction: column;
}

:deep(.p-card-content) {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem;
}

/* Garantir que os gráficos ApexCharts sejam responsivos */
:deep(.apexcharts-canvas) {
    width: 100% !important;
}

:deep(.apexcharts-svg) {
    width: 100% !important;
}
</style>
