@extends('admin.layouts.app')

@section('title', 'Peta Jabatan - ' . $opd->nama)
@section('page-title', 'Peta Jabatan')

@push('styles')
<style>
    #canvas-container {
        width: 100%;
        height: calc(100vh - 240px);
        min-height: 600px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        background-image:
            radial-gradient(circle, #cbd5e1 1px, transparent 1px);
        background-size: 24px 24px;
        position: relative;
        overflow: hidden;
        cursor: grab;
        border-radius: 0.5rem;
    }

    #canvas-container.grabbing {
        cursor: grabbing;
    }

    .canvas-controls {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        z-index: 10;
    }

    .canvas-controls button {
        width: 32px;
        height: 32px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, transform 0.1s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        color: #475569;
    }

    .canvas-controls button:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #1e293b;
    }

    .canvas-controls button:active {
        transform: scale(0.96);
    }

    .canvas-controls .controls-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0.125rem 0;
    }

    .zoom-level {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.375rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: #64748b;
        z-index: 10;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        font-family: 'Inter', ui-sans-serif, sans-serif;
    }

    @media print {
        /* Reset body */
        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Hide sidebar */
        body > div > div.fixed {
            display: none !important;
        }

        /* Hide header/topbar */
        header {
            display: none !important;
        }

        /* Hide navigation elements */
        .no-print,
        nav,
        .canvas-controls,
        .zoom-level {
            display: none !important;
        }

        /* Reset main content area */
        .lg\:ml-64 {
            margin-left: 0 !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
        }

        .p-4, .lg\:p-8 {
            padding: 0 !important;
        }

        /* Make canvas container full page */
        #canvas-container {
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            border: none !important;
            overflow: visible !important;
            page-break-inside: avoid;
        }

        /* Hide shadow on wrapper */
        .bg-white.rounded-lg.shadow-sm {
            box-shadow: none !important;
            padding: 5mm !important;
        }

        /* Print header for the chart */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .print-header h1 {
            font-size: 16pt !important;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .print-header p {
            font-size: 12pt !important;
            color: #666;
            margin: 0;
        }

        /* Show print image, hide canvas */
        #print-image-container {
            display: block !important;
            width: 100% !important;
            text-align: center !important;
            page-break-inside: avoid;
        }

        #print-image {
            max-width: 100% !important;
            max-height: 100% !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            display: block !important;
            margin: 0 auto !important;
        }

        #canvas-container {
            display: none !important;
        }

        /* Default A4 landscape */
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
    }

    /* For portrait mode printing */
    @media print and (orientation: portrait) {
        @page {
            size: A4 portrait;
            margin: 5mm;
        }
    }

    /* For larger charts - use A3 */
    @media print and (min-width: 1000px) {
        @page {
            size: A3 landscape;
            margin: 5mm;
        }
    }

    .print-header {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-5 flex justify-between items-start no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.opds.show', $opd->id) }}"
               class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-gray-800 hover:border-gray-300 hover:bg-gray-50 transition-all duration-150">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="16" data-height="16"></span>
            </a>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Peta Jabatan</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $opd->nama }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="printCanvas('landscape')"
                    class="btn btn-outline text-sm gap-1.5">
                <span class="iconify" data-icon="mdi:printer" data-width="16" data-height="16"></span>
                Cetak
            </button>
            <button onclick="exportCanvas()"
                    class="btn btn-outline text-sm gap-1.5">
                <span class="iconify" data-icon="mdi:download" data-width="16" data-height="16"></span>
                Export PNG
            </button>
            <a href="{{ route('admin.opds.peta-jabatan.export-excel', $opd->id) }}"
               class="btn btn-success text-sm gap-1.5">
                <span class="iconify" data-icon="mdi:microsoft-excel" data-width="16" data-height="16"></span>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Organizational Chart Canvas -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Print Header (only visible when printing) -->
        <div class="print-header">
            <h1 style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">Peta Jabatan</h1>
            <p style="font-size: 14px; color: #666;">{{ $opd->nama }}</p>
        </div>

        <!-- Print Image Container (only visible when printing) -->
        <div id="print-image-container" style="display: none; text-align: center;">
            <img id="print-image" style="max-width: 100%; height: auto;" />
        </div>

        @if($opd->jabatanKepala->count() > 0)
            <div id="canvas-container">
                <div class="canvas-controls no-print">
                    <button onclick="zoomIn()" title="Perbesar">
                        <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                    </button>
                    <button onclick="zoomOut()" title="Perkecil">
                        <span class="iconify" data-icon="mdi:minus" data-width="16" data-height="16"></span>
                    </button>
                    <div class="controls-divider"></div>
                    <button onclick="resetZoom()" title="Reset Tampilan">
                        <span class="iconify" data-icon="mdi:fit-to-screen" data-width="16" data-height="16"></span>
                    </button>
                </div>
                <div class="zoom-level no-print"><span id="zoom-text">100</span>%</div>
                <div id="konva-stage"></div>
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="iconify text-gray-400" data-icon="mdi:file-tree-outline" data-width="32" data-height="32"></span>
                </div>
                <p class="text-gray-500 text-sm">Belum ada struktur organisasi untuk</p>
                <p class="text-gray-700 font-medium mt-0.5">{{ $opd->nama }}</p>
                <div class="mt-6 no-print">
                    <a href="{{ route('admin.opds.show', $opd->id) }}" class="btn btn-primary text-sm gap-1.5">
                        <span class="iconify" data-icon="mdi:plus" data-width="16" data-height="16"></span>
                        Tambah Jabatan
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<!-- Konva.js -->
<script src="https://unpkg.com/konva@9/konva.min.js"></script>

<script>
// Data dari Laravel
const orgData = @json($opd->jabatanKepala);

// Configuration
const CONFIG = {
    boxWidth: 210,
    boxHeight: 90,
    tableRowHeight: 22,
    horizontalGap: 48,
    verticalGap: 44,
    fontSize: 11,
    headerFontSize: 10,
    tableFontSize: 9,
    padding: 10,
    radius: 6,
    minZoom: 0.2,
    maxZoom: 2.5,
    zoomStep: 0.15,

    // Color palette — derived from project primary (primary-600 #667eea)
    colors: {
        struktural:   { header: '#1e293b', headerText: '#f8fafc', border: '#334155', body: '#ffffff', namaText: '#0f172a', kelasText: '#475569' },
        kepala:       { header: '#1e3a5f', headerText: '#f0f9ff', border: '#1e4a7a', body: '#f8fcff', namaText: '#0c2340', kelasText: '#3b82f6' },
        fungsional:   { header: '#14532d', headerText: '#f0fdf4', border: '#166534', body: '#ffffff', namaText: '#0f172a', kelasText: '#475569' },
        connector:    '#94a3b8',
        tableHeaderBg: '#1e293b',
        tableHeaderText: '#f8fafc',
        tableColBg:   '#f1f5f9',
        tableColText: '#334155',
        tableRowAlt:  '#f8fafc',
        tableRowText: '#1e293b',
        tableBorder:  '#e2e8f0',
        selisihPos:   '#16a34a',
        selisihNeg:   '#dc2626',
        selisihZero:  '#64748b',
    }
};

let stage, layer;
let currentScale = 1;

// Initialize Konva Stage
function initStage() {
    const container = document.getElementById('canvas-container');
    const width = container.offsetWidth;
    const height = container.offsetHeight;

    stage = new Konva.Stage({
        container: 'konva-stage',
        width: width,
        height: height,
        draggable: true
    });

    layer = new Konva.Layer();
    stage.add(layer);

    // Add event listeners
    setupEventListeners();
}

// Setup event listeners for dragging
function setupEventListeners() {
    const container = document.getElementById('canvas-container');

    stage.on('dragstart', () => {
        container.classList.add('grabbing');
    });

    stage.on('dragend', () => {
        container.classList.remove('grabbing');
    });

    // Mouse wheel zoom
    stage.on('wheel', (e) => {
        e.evt.preventDefault();

        const oldScale = stage.scaleX();
        const pointer = stage.getPointerPosition();

        const mousePointTo = {
            x: (pointer.x - stage.x()) / oldScale,
            y: (pointer.y - stage.y()) / oldScale
        };

        const direction = e.evt.deltaY > 0 ? -1 : 1;
        const newScale = direction > 0 ? oldScale * 1.1 : oldScale / 1.1;

        if (newScale >= CONFIG.minZoom && newScale <= CONFIG.maxZoom) {
            currentScale = newScale;
            stage.scale({ x: newScale, y: newScale });

            const newPos = {
                x: pointer.x - mousePointTo.x * newScale,
                y: pointer.y - mousePointTo.y * newScale
            };

            stage.position(newPos);
            updateZoomText();
        }
    });
}

// Zoom functions
function zoomIn() {
    const newScale = Math.min(currentScale + CONFIG.zoomStep, CONFIG.maxZoom);
    setZoom(newScale);
}

function zoomOut() {
    const newScale = Math.max(currentScale - CONFIG.zoomStep, CONFIG.minZoom);
    setZoom(newScale);
}

function setZoom(scale) {
    const center = {
        x: stage.width() / 2,
        y: stage.height() / 2
    };

    const oldScale = stage.scaleX();
    const mousePointTo = {
        x: (center.x - stage.x()) / oldScale,
        y: (center.y - stage.y()) / oldScale
    };

    currentScale = scale;
    stage.scale({ x: scale, y: scale });

    const newPos = {
        x: center.x - mousePointTo.x * scale,
        y: center.y - mousePointTo.y * scale
    };

    stage.position(newPos);
    updateZoomText();
}

function resetZoom() {
    currentScale = 1;
    stage.scale({ x: 1, y: 1 });
    stage.position({ x: stage.width() / 2, y: 50 });
    updateZoomText();
}

function updateZoomText() {
    document.getElementById('zoom-text').textContent = Math.round(currentScale * 100);
}

// Process organization data
function processOrgData(nodes, parentNode = null) {
    const processed = [];

    nodes.forEach(node => {
        const processedNode = {
            id: node.id,
            nama: node.nama,
            jenis_jabatan: node.jenis_jabatan,
            kelas: node.kelas,
            kebutuhan: node.kebutuhan,
            bezetting: node.asns ? node.asns.length : 0,
            selisih: (node.asns ? node.asns.length : 0) - node.kebutuhan,
            parent: parentNode,
            children: [],
            // Calculate height for this node
            layoutHeight: calculateNodeHeight(node)
        };

        if (node.children && node.children.length > 0) {
            processedNode.children = processOrgData(node.children, processedNode);
        }

        processed.push(processedNode);
    });

    return processed;
}

// Calculate the height of a node before rendering
function calculateNodeHeight(node) {
    const headerHeight = 28;
    const minNamaHeight = 32;
    const kelasHeight = 24;

    const tempText = new Konva.Text({
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        text: node.nama,
        fontSize: CONFIG.fontSize,
        fontFamily: 'Inter, Arial, sans-serif',
        wrap: 'word',
        lineHeight: 1.35
    });

    const actualNamaHeight = Math.max(minNamaHeight, tempText.height() + CONFIG.padding);
    return headerHeight + actualNamaHeight + kelasHeight;
}

// Helper: Calculate table height
function calculateTableHeight(items) {
    const headerHeight = 28;
    const columnHeaderHeight = 26;
    const minRowHeight = CONFIG.tableRowHeight;
    const rowHeights = items.map(item => {
        const tempText = new Konva.Text({
            width: 196,
            text: item.nama,
            fontSize: 9,
            fontFamily: 'Inter, Arial, sans-serif',
            wrap: 'word',
            lineHeight: 1.3
        });
        return Math.max(minRowHeight, tempText.height() + 6);
    });
    return headerHeight + columnHeaderHeight + rowHeights.reduce((sum, h) => sum + h, 0);
}

// Layout B: Tables below parent, then struktural children below tables
function calculateLayout(nodes, x = 0, y = 0, level = 0) {
    // Separate nodes into struktural and non-struktural
    const strukturalNodes = [];
    const pelaksanaNodes = [];
    const fungsionalNodes = [];

    nodes.forEach(node => {
        if (node.jenis_jabatan === 'Pelaksana') {
            pelaksanaNodes.push(node);
        } else if (node.jenis_jabatan === 'Fungsional') {
            fungsionalNodes.push(node);
        } else {
            strukturalNodes.push(node);
        }
    });

    // Create table objects (Pelaksana first, then Fungsional)
    const tableObjects = [];

    if (pelaksanaNodes.length > 0) {
        tableObjects.push({
            isTable: true,
            jenis_jabatan: 'Pelaksana',
            items: pelaksanaNodes,
            layoutWidth: 360,
            layoutHeight: calculateTableHeight(pelaksanaNodes)
        });
    }

    if (fungsionalNodes.length > 0) {
        tableObjects.push({
            isTable: true,
            jenis_jabatan: 'Fungsional',
            items: fungsionalNodes,
            layoutWidth: 360,
            layoutHeight: calculateTableHeight(fungsionalNodes)
        });
    }

    const positions = [];

    // Step 1: Calculate layoutWidth for each struktural node
    strukturalNodes.forEach((node) => {
        let nodeWidth = CONFIG.boxWidth;
        if (node.children && node.children.length > 0) {
            const childLayout = calculateLayout(node.children, 0, 0, level + 1);
            node.childLayout = childLayout;
            nodeWidth = Math.max(nodeWidth, childLayout.totalWidth);
        }
        node.layoutWidth = nodeWidth;
    });

    // Step 2: Build a flat list of all children (tabel + struktural) in order
    // Tables go after struktural, side by side horizontally
    const allItems = [];

    strukturalNodes.forEach((node) => {
        allItems.push({ type: 'struktural', node: node, width: node.layoutWidth });
    });

    tableObjects.forEach((table) => {
        allItems.push({ type: 'table', node: table, width: table.layoutWidth });
    });

    // Step 3: Compute total width of all items
    let totalWidth = 0;
    allItems.forEach((item, index) => {
        if (index > 0) totalWidth += CONFIG.horizontalGap;
        totalWidth += item.width;
    });

    // Step 4: Position all items horizontally centered at x
    let currentX = x - totalWidth / 2;

    allItems.forEach((item, index) => {
        if (index > 0) currentX += CONFIG.horizontalGap;

        const itemX = currentX + item.width / 2;
        const itemY = y;

        positions.push({
            node: item.node,
            x: itemX,
            y: itemY,
            isTable: item.type === 'table'
        });

        // Recursively position struktural children below their parent
        if (item.type === 'struktural' && item.node.childLayout) {
            const nodeHeight = item.node.layoutHeight || CONFIG.boxHeight;
            const childY = itemY + nodeHeight + CONFIG.verticalGap;
            item.node.childPositions = calculateLayout(item.node.children, itemX, childY, level + 1).positions;
        }

        currentX += item.width;
    });

    return {
        positions: positions,
        totalWidth: totalWidth
    };
}

// Draw box node — polished card style
function drawBoxNode(node, x, y) {
    const headerHeight = 28;
    const minNamaHeight = 32;
    const kelasHeight = 24;
    const font = 'Inter, Arial, sans-serif';

    // Pick color scheme based on jenis_jabatan
    let scheme = CONFIG.colors.struktural;
    if (node.jenis_jabatan === 'Kepala' || node.jenis_jabatan === 'Kepala OPD') {
        scheme = CONFIG.colors.kepala;
    } else if (node.jenis_jabatan === 'Fungsional') {
        scheme = CONFIG.colors.fungsional;
    }

    const namaText = new Konva.Text({
        x: CONFIG.padding,
        y: 0,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        text: node.nama,
        fontSize: CONFIG.fontSize,
        fontFamily: font,
        fill: scheme.namaText,
        align: 'center',
        verticalAlign: 'middle',
        wrap: 'word',
        lineHeight: 1.35
    });

    const actualNamaHeight = Math.max(minNamaHeight, namaText.height() + CONFIG.padding);
    const totalHeight = headerHeight + actualNamaHeight + kelasHeight;

    const group = new Konva.Group({ x: x - CONFIG.boxWidth / 2, y: y });

    // Drop shadow
    const shadow = new Konva.Rect({
        x: 2,
        y: 2,
        width: CONFIG.boxWidth,
        height: totalHeight,
        cornerRadius: CONFIG.radius,
        fill: 'rgba(0,0,0,0.08)'
    });
    group.add(shadow);

    // Main card
    const box = new Konva.Rect({
        width: CONFIG.boxWidth,
        height: totalHeight,
        cornerRadius: CONFIG.radius,
        stroke: scheme.border,
        strokeWidth: 1,
        fill: scheme.body
    });
    group.add(box);

    // Header rounded top only — draw as rect clipped to top
    const header = new Konva.Rect({
        width: CONFIG.boxWidth,
        height: headerHeight,
        cornerRadius: [CONFIG.radius, CONFIG.radius, 0, 0],
        fill: scheme.header
    });
    group.add(header);

    const headerText = new Konva.Text({
        x: CONFIG.padding,
        y: 0,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        height: headerHeight,
        text: node.jenis_jabatan,
        fontSize: 9,
        fontFamily: font,
        fill: scheme.headerText,
        align: 'center',
        verticalAlign: 'middle',
        fontStyle: 'bold',
        letterSpacing: 0.5
    });
    group.add(headerText);

    // Divider below header
    const divider = new Konva.Line({
        points: [0, headerHeight, CONFIG.boxWidth, headerHeight],
        stroke: scheme.border,
        strokeWidth: 0.5,
        opacity: 0.4
    });
    group.add(divider);

    // Nama
    namaText.y(headerHeight + (actualNamaHeight - namaText.height()) / 2);
    group.add(namaText);

    // Divider before kelas
    const kelasDiv = new Konva.Line({
        points: [CONFIG.padding, headerHeight + actualNamaHeight, CONFIG.boxWidth - CONFIG.padding, headerHeight + actualNamaHeight],
        stroke: '#e2e8f0',
        strokeWidth: 1
    });
    group.add(kelasDiv);

    // Kelas badge area
    const kelasText = new Konva.Text({
        x: CONFIG.padding,
        y: headerHeight + actualNamaHeight,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        height: kelasHeight,
        text: node.kelas ? 'Kelas ' + node.kelas : '—',
        fontSize: 10,
        fontFamily: font,
        fill: scheme.kelasText,
        align: 'center',
        verticalAlign: 'middle',
        fontStyle: node.kelas ? 'bold' : 'normal'
    });
    group.add(kelasText);

    layer.add(group);
    return { width: CONFIG.boxWidth, height: totalHeight, centerX: x, bottomY: y + totalHeight };
}

// Draw table node — polished card style
function drawTableNode(jenis, items, x, y) {
    const tableWidth = 360;
    const headerHeight = 28;
    const columnHeaderHeight = 26;
    const minRowHeight = CONFIG.tableRowHeight;
    const font = 'Inter, Arial, sans-serif';

    const isFungsional = jenis === 'Fungsional';
    const headerBg = isFungsional ? '#14532d' : '#1e293b';

    const rowHeights = items.map(item => {
        const namaText = new Konva.Text({
            width: 196,
            text: item.nama,
            fontSize: 9,
            fontFamily: font,
            wrap: 'word',
            lineHeight: 1.3
        });
        return Math.max(minRowHeight, namaText.height() + 6);
    });

    const totalHeight = headerHeight + columnHeaderHeight + rowHeights.reduce((sum, h) => sum + h, 0);

    const group = new Konva.Group({ x: x - tableWidth / 2, y: y });

    // Shadow
    const shadow = new Konva.Rect({
        x: 2, y: 2,
        width: tableWidth,
        height: totalHeight,
        cornerRadius: CONFIG.radius,
        fill: 'rgba(0,0,0,0.07)'
    });
    group.add(shadow);

    // Main border
    const border = new Konva.Rect({
        width: tableWidth,
        height: totalHeight,
        cornerRadius: CONFIG.radius,
        stroke: '#cbd5e1',
        strokeWidth: 1,
        fill: '#ffffff'
    });
    group.add(border);

    // Header
    const header = new Konva.Rect({
        width: tableWidth,
        height: headerHeight,
        cornerRadius: [CONFIG.radius, CONFIG.radius, 0, 0],
        fill: headerBg
    });
    group.add(header);

    const headerText = new Konva.Text({
        width: tableWidth,
        height: headerHeight,
        text: 'Jabatan ' + jenis,
        fontSize: 10,
        fontFamily: font,
        fill: '#f8fafc',
        align: 'center',
        verticalAlign: 'middle',
        fontStyle: 'bold',
        letterSpacing: 0.5
    });
    group.add(headerText);

    // Column header background
    const colHeaderY = headerHeight;
    const colHeaderBg = new Konva.Rect({
        y: colHeaderY,
        width: tableWidth,
        height: columnHeaderHeight,
        fill: CONFIG.colors.tableColBg
    });
    group.add(colHeaderBg);

    // Column header bottom border
    group.add(new Konva.Line({
        points: [0, colHeaderY + columnHeaderHeight, tableWidth, colHeaderY + columnHeaderHeight],
        stroke: '#cbd5e1',
        strokeWidth: 1
    }));

    // Column widths
    const colWidths = { nama: 196, kelas: 46, b: 30, k: 30, s: 30 };
    // Tooltip labels for compact headers
    const columns = [
        { label: 'Nama Jabatan',  width: colWidths.nama,  abbr: false },
        { label: 'Kls',           width: colWidths.kelas, abbr: false },
        { label: 'B',             width: colWidths.b,     abbr: true  },
        { label: 'K',             width: colWidths.k,     abbr: true  },
        { label: 'S',             width: colWidths.s,     abbr: true  }
    ];

    let colX = 0;
    columns.forEach((col, idx) => {
        if (idx > 0) {
            group.add(new Konva.Line({
                points: [colX, colHeaderY, colX, totalHeight],
                stroke: '#e2e8f0',
                strokeWidth: 1
            }));
        }

        group.add(new Konva.Text({
            x: colX + 3,
            y: colHeaderY,
            width: col.width - 6,
            height: columnHeaderHeight,
            text: col.label,
            fontSize: 8,
            fontFamily: font,
            fill: CONFIG.colors.tableColText,
            align: 'center',
            verticalAlign: 'middle',
            fontStyle: 'bold'
        }));

        colX += col.width;
    });

    // Data rows
    let currentRowY = headerHeight + columnHeaderHeight;
    items.forEach((item, idx) => {
        const rowHeight = rowHeights[idx];
        const isAlt = idx % 2 === 1;

        // Alternating row background
        if (isAlt) {
            group.add(new Konva.Rect({
                y: currentRowY,
                width: tableWidth,
                height: rowHeight,
                fill: CONFIG.colors.tableRowAlt
            }));
        }

        // Row divider
        group.add(new Konva.Line({
            points: [0, currentRowY, tableWidth, currentRowY],
            stroke: '#e2e8f0',
            strokeWidth: 0.5
        }));

        let cellX = 0;
        const selisih = item.selisih;
        const selisihColor = selisih < 0
            ? CONFIG.colors.selisihNeg
            : selisih > 0
                ? CONFIG.colors.selisihPos
                : CONFIG.colors.selisihZero;

        const rowData = [
            { text: item.nama,   width: colWidths.nama,  align: 'left',   wrap: true,  color: CONFIG.colors.tableRowText },
            { text: item.kelas || '—', width: colWidths.kelas, align: 'center', wrap: false, color: '#475569' },
            { text: String(item.bezetting), width: colWidths.b, align: 'center', wrap: false, color: CONFIG.colors.tableRowText },
            { text: String(item.kebutuhan), width: colWidths.k, align: 'center', wrap: false, color: CONFIG.colors.tableRowText },
            { text: (selisih >= 0 ? '+' : '') + selisih, width: colWidths.s, align: 'center', wrap: false, color: selisihColor }
        ];

        rowData.forEach(cell => {
            group.add(new Konva.Text({
                x: cellX + 4,
                y: currentRowY + 3,
                width: cell.width - 8,
                height: rowHeight - 6,
                text: cell.text,
                fontSize: 9,
                fontFamily: font,
                fill: cell.color,
                align: cell.align,
                verticalAlign: 'middle',
                wrap: cell.wrap ? 'word' : 'none',
                lineHeight: 1.3,
                fontStyle: cell === rowData[4] && selisih !== 0 ? 'bold' : 'normal'
            }));
            cellX += cell.width;
        });

        currentRowY += rowHeight;
    });

    layer.add(group);
    return { width: tableWidth, height: totalHeight, centerX: x, bottomY: y + totalHeight };
}

// Draw connector lines — soft slate color, rounded joints
function drawConnector(fromX, fromY, toX, toY) {
    const color = CONFIG.colors.connector;
    const sw = 1.5;

    if (Math.abs(fromX - toX) < 5) {
        layer.add(new Konva.Line({
            points: [fromX, fromY, toX, toY],
            stroke: color,
            strokeWidth: sw,
            lineCap: 'round'
        }));
    } else {
        const midY = fromY + (toY - fromY) / 2;
        layer.add(new Konva.Line({
            points: [fromX, fromY, fromX, midY, toX, midY, toX, toY],
            stroke: color,
            strokeWidth: sw,
            lineCap: 'round',
            lineJoin: 'round',
            tension: 0
        }));
    }
}

// Render the tree
function renderTree(positions, parentInfo = null) {
    // Draw all connectors first (so lines appear behind nodes)
    positions.forEach(pos => {
        if (parentInfo) {
            drawConnector(parentInfo.centerX, parentInfo.bottomY, pos.x, pos.y);
        }
    });

    // Then draw all nodes on top
    positions.forEach(pos => {
        const { node, x, y } = pos;

        if (pos.isTable) {
            drawTableNode(node.jenis_jabatan, node.items, x, y);
        } else {
            const nodeInfo = drawBoxNode(node, x, y);
            if (node.childPositions) {
                renderTree(node.childPositions, nodeInfo);
            }
        }
    });
}

// Export canvas to PNG
function exportCanvas() {
    const uri = stage.toDataURL({ pixelRatio: 2 });
    const link = document.createElement('a');
    link.download = 'peta-jabatan-{{ Str::slug($opd->nama) }}.png';
    link.href = uri;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print canvas
function printCanvas(orientation = 'landscape') {
    // Get the full canvas content bounds
    const layerRect = layer.getClientRect({ relativeTo: layer });

    // Add padding around content
    const padding = 40;
    const contentWidth = Math.ceil(layerRect.width + padding * 2);
    const contentHeight = Math.ceil(layerRect.height + padding * 2);

    // Save current stage state
    const oldScale = stage.scaleX();
    const oldX = stage.x();
    const oldY = stage.y();

    // Reset stage transform for export
    stage.scale({ x: 1, y: 1 });
    stage.position({ x: -layerRect.x + padding, y: -layerRect.y + padding });

    // Temporarily resize stage to fit content
    const oldWidth = stage.width();
    const oldHeight = stage.height();
    stage.width(contentWidth);
    stage.height(contentHeight);

    // Use higher pixel ratio for better quality
    const pixelRatio = Math.min(3, window.devicePixelRatio || 2);

    // Generate image with all content visible
    const uri = stage.toDataURL({
        pixelRatio: pixelRatio,
        mimeType: 'image/png',
        quality: 1
    });

    // Restore stage state
    stage.width(oldWidth);
    stage.height(oldHeight);
    stage.scale({ x: oldScale, y: oldScale });
    stage.position({ x: oldX, y: oldY });

    // Set the print image
    const printImg = document.getElementById('print-image');
    printImg.src = uri;

    // Determine best page size based on content dimensions
    const aspectRatio = contentWidth / contentHeight;
    let pageSize, pageMargin;

    // A4 dimensions in mm
    const A4_WIDTH = 297;
    const A4_HEIGHT = 210;

    // A3 dimensions in mm
    const A3_WIDTH = 420;
    const A3_HEIGHT = 297;

    // Determine if we need A3 based on content size
    const needsA3 = contentWidth > 2000 || contentHeight > 1500;

    if (needsA3) {
        pageSize = aspectRatio > 1 ? 'A3 landscape' : 'A3 portrait';
        pageMargin = '5mm';
    } else {
        pageSize = aspectRatio > 1 ? 'A4 landscape' : 'A4 portrait';
        pageMargin = '5mm';
    }

    // Update print styles
    let styleEl = document.getElementById('print-orientation-style');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'print-orientation-style';
        document.head.appendChild(styleEl);
    }

    styleEl.textContent = `
        @media print {
            @page {
                size: ${pageSize};
                margin: ${pageMargin};
            }
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            #print-image {
                max-width: 100% !important;
                max-height: calc(100vh - 30mm) !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                display: block !important;
                margin: 0 auto !important;
            }
            .print-header {
                page-break-after: avoid;
            }
            #print-image-container {
                page-break-inside: avoid;
            }
        }
    `;

    // Wait for image to load, then print
    printImg.onload = function() {
        setTimeout(() => {
            window.print();
        }, 300);
    };

    // Fallback timeout in case onload doesn't fire
    setTimeout(() => {
        window.print();
    }, 800);
}

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    if (orgData && orgData.length > 0) {
        initStage();

        const processedData = processOrgData(orgData);
        const layout = calculateLayout(processedData, stage.width() / 2, 50, 0);

        renderTree(layout.positions);

        layer.draw();

        // Center the view
        resetZoom();
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (stage) {
        const container = document.getElementById('canvas-container');
        stage.width(container.offsetWidth);
        stage.height(container.offsetHeight);
    }
});
</script>
@endpush
