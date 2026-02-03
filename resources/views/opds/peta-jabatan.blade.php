@extends('admin.layouts.app')

@section('title', 'Peta Jabatan - ' . $opd->nama)
@section('page-title', 'Peta Jabatan')

@push('styles')
<style>
    #canvas-container {
        width: 100%;
        height: calc(100vh - 250px);
        min-height: 600px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        position: relative;
        overflow: hidden;
        cursor: grab;
    }

    #canvas-container.grabbing {
        cursor: grabbing;
    }

    .canvas-controls {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 0.5rem;
        z-index: 10;
    }

    .canvas-controls button {
        width: 36px;
        height: 36px;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .canvas-controls button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .canvas-controls button:active {
        transform: scale(0.95);
    }

    .zoom-level {
        position: absolute;
        bottom: 1rem;
        right: 1rem;
        background: white;
        border: 1px solid #d1d5db;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        color: #6b7280;
        z-index: 10;
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
    <div class="mb-6 flex justify-between items-center no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.opds.show', $opd->id) }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Peta Jabatan</h2>
                <p class="text-gray-600">{{ $opd->nama }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="printCanvas('landscape')" class="btn btn-outline">
                <span class="iconify" data-icon="mdi:printer" data-width="18" data-height="18"></span>
                <span class="ml-2">Cetak</span>
            </button>
            <button onclick="exportCanvas()" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                <span class="ml-2">Export PNG</span>
            </button>
            <a href="{{ route('admin.opds.peta-jabatan.export-excel', $opd->id) }}" class="btn btn-success">
                <span class="iconify" data-icon="mdi:microsoft-excel" data-width="18" data-height="18"></span>
                <span class="ml-2">Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Organizational Chart Canvas -->
    <div class="bg-white rounded-lg shadow-sm p-6">
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
                    <button onclick="zoomIn()" title="Zoom In">
                        <span class="iconify" data-icon="mdi:plus" data-width="20" data-height="20"></span>
                    </button>
                    <button onclick="zoomOut()" title="Zoom Out">
                        <span class="iconify" data-icon="mdi:minus" data-width="20" data-height="20"></span>
                    </button>
                    <button onclick="resetZoom()" title="Reset View">
                        <span class="iconify" data-icon="mdi:fit-to-screen" data-width="20" data-height="20"></span>
                    </button>
                </div>
                <div class="zoom-level no-print">Zoom: <span id="zoom-text">100%</span></div>
                <div id="konva-stage"></div>
            </div>
        @else
            <div class="text-center py-12">
                <span class="iconify text-gray-300" data-icon="mdi:file-tree" data-width="64" data-height="64"></span>
                <p class="mt-4 text-gray-500">
                    Belum ada struktur organisasi untuk {{ $opd->nama }}
                </p>
                <div class="mt-6 no-print">
                    <a href="{{ route('admin.opds.show', $opd->id) }}" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
                        <span class="ml-2">Tambah Jabatan</span>
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
    boxWidth: 200,
    boxHeight: 80, // header(25) + nama(30) + kelas(25)
    tableRowHeight: 20,
    horizontalGap: 60,
    verticalGap: 40,
    fontSize: 12,
    headerFontSize: 11,
    tableFontSize: 10,
    padding: 10,
    minZoom: 0.3,
    maxZoom: 2,
    zoomStep: 0.1
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
    document.getElementById('zoom-text').textContent = Math.round(currentScale * 100) + '%';
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
    const headerHeight = 25;
    const minNamaHeight = 30;
    const kelasHeight = 25;

    // Create temporary Konva.Text to measure actual height
    const tempText = new Konva.Text({
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        text: node.nama,
        fontSize: CONFIG.fontSize,
        fontFamily: 'Arial',
        wrap: 'word',
        lineHeight: 1.2
    });

    const actualNamaHeight = Math.max(minNamaHeight, tempText.height() + CONFIG.padding);
    return headerHeight + actualNamaHeight + kelasHeight;
}

// Helper: Calculate table height
function calculateTableHeight(items) {
    const headerHeight = 25;
    const columnHeaderHeight = 25;
    const minRowHeight = CONFIG.tableRowHeight;
    const rowHeights = items.map(item => {
        const tempText = new Konva.Text({
            width: 210,
            text: item.nama,
            fontSize: 8,
            fontFamily: 'Arial',
            wrap: 'word',
            lineHeight: 1.2
        });
        return Math.max(minRowHeight, tempText.height() + 4);
    });
    return headerHeight + columnHeaderHeight + rowHeights.reduce((sum, h) => sum + h, 0);
}

// Calculate tree layout - each struktural node has its tables directly below it
function calculateLayout(nodes, x = 0, y = 0, level = 0) {
    // Separate nodes into struktural and non-struktural (tables)
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

    const positions = [];
    let totalWidth = 0;

    // First pass: calculate width needed for each struktural node (including its subtree)
    strukturalNodes.forEach((node, index) => {
        // Separate this node's children
        const childStrukturals = [];
        const childPelaksana = [];
        const childFungsional = [];

        if (node.children) {
            node.children.forEach(child => {
                if (child.jenis_jabatan === 'Pelaksana') {
                    childPelaksana.push(child);
                } else if (child.jenis_jabatan === 'Fungsional') {
                    childFungsional.push(child);
                } else {
                    childStrukturals.push(child);
                }
            });
        }

        // Store separated children
        node.childStrukturals = childStrukturals;
        node.childPelaksana = childPelaksana;
        node.childFungsional = childFungsional;

        // Calculate width needed
        let nodeWidth = CONFIG.boxWidth;

        // Consider table width
        if (childPelaksana.length > 0 || childFungsional.length > 0) {
            nodeWidth = Math.max(nodeWidth, 350);
        }

        // Consider struktural children width
        if (childStrukturals.length > 0) {
            const childLayout = calculateLayout(childStrukturals, 0, 0, level + 1);
            node.childLayout = childLayout;
            nodeWidth = Math.max(nodeWidth, childLayout.totalWidth);
        }

        node.layoutWidth = nodeWidth;
        totalWidth += nodeWidth;

        if (index > 0) {
            totalWidth += CONFIG.horizontalGap;
        }
    });

    // Handle case where only tables exist (no struktural nodes)
    if (strukturalNodes.length === 0) {
        if (pelaksanaNodes.length > 0) {
            const pelaksanaTable = {
                isTable: true,
                jenis_jabatan: 'Pelaksana',
                items: pelaksanaNodes,
                layoutWidth: 350,
                layoutHeight: calculateTableHeight(pelaksanaNodes)
            };
            positions.push({
                node: pelaksanaTable,
                x: x,
                y: y,
                isTable: true
            });
            y += pelaksanaTable.layoutHeight + CONFIG.verticalGap;
        }

        if (fungsionalNodes.length > 0) {
            const fungsionalTable = {
                isTable: true,
                jenis_jabatan: 'Fungsional',
                items: fungsionalNodes,
                layoutWidth: 350,
                layoutHeight: calculateTableHeight(fungsionalNodes)
            };
            positions.push({
                node: fungsionalTable,
                x: x,
                y: y,
                isTable: true
            });
        }

        return {
            positions: positions,
            totalWidth: Math.max(totalWidth, 350)
        };
    }

    // Second pass: position struktural nodes horizontally
    let currentX = x - totalWidth / 2;

    strukturalNodes.forEach((node, index) => {
        if (index > 0) {
            currentX += CONFIG.horizontalGap;
        }

        const nodeX = currentX + node.layoutWidth / 2;
        const nodeY = y;
        const nodeHeight = node.layoutHeight || CONFIG.boxHeight;

        positions.push({
            node: node,
            x: nodeX,
            y: nodeY
        });

        // Track Y position for elements below this node
        let belowY = nodeY + nodeHeight + CONFIG.verticalGap;

        // Add tables for this node's non-struktural children (directly below the struktural box)
        if (node.childPelaksana.length > 0) {
            const pelaksanaTable = {
                isTable: true,
                jenis_jabatan: 'Pelaksana',
                items: node.childPelaksana,
                layoutWidth: 350,
                layoutHeight: calculateTableHeight(node.childPelaksana)
            };
            positions.push({
                node: pelaksanaTable,
                x: nodeX,
                y: belowY,
                isTable: true,
                parentX: nodeX,
                parentBottomY: nodeY + nodeHeight
            });
            belowY += pelaksanaTable.layoutHeight + CONFIG.verticalGap;
        }

        if (node.childFungsional.length > 0) {
            const fungsionalTable = {
                isTable: true,
                jenis_jabatan: 'Fungsional',
                items: node.childFungsional,
                layoutWidth: 350,
                layoutHeight: calculateTableHeight(node.childFungsional)
            };
            positions.push({
                node: fungsionalTable,
                x: nodeX,
                y: belowY,
                isTable: true,
                parentX: nodeX,
                parentBottomY: nodeY + nodeHeight
            });
            belowY += fungsionalTable.layoutHeight + CONFIG.verticalGap;
        }

        // Position struktural children below tables
        if (node.childLayout && node.childStrukturals.length > 0) {
            node.childPositions = calculateLayout(node.childStrukturals, nodeX, belowY, level + 1).positions;
        }

        currentX += node.layoutWidth;
    });

    return {
        positions: positions,
        totalWidth: totalWidth
    };
}

// Draw box node (Format: Header hitam + Nama + Kelas)
function drawBoxNode(node, x, y) {
    const headerHeight = 25;
    const minNamaHeight = 30;
    const kelasHeight = 25;

    // Nama Jabatan - with wrapping for long names
    const namaText = new Konva.Text({
        x: CONFIG.padding,
        y: 0,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        text: node.nama,
        fontSize: CONFIG.fontSize,
        fontFamily: 'Arial',
        fill: '#000000',
        align: 'center',
        verticalAlign: 'middle',
        wrap: 'word',
        lineHeight: 1.2
    });

    // Calculate actual nama height based on text
    const actualNamaHeight = Math.max(minNamaHeight, namaText.height() + CONFIG.padding);
    const totalHeight = headerHeight + actualNamaHeight + kelasHeight;

    const group = new Konva.Group({ x: x - CONFIG.boxWidth / 2, y: y });

    // Main box border
    const box = new Konva.Rect({
        width: CONFIG.boxWidth,
        height: totalHeight,
        stroke: '#000000',
        strokeWidth: 2,
        fill: '#FFFFFF'
    });
    group.add(box);

    // Header: Jenis Jabatan (background hitam, text putih)
    const header = new Konva.Rect({
        width: CONFIG.boxWidth,
        height: headerHeight,
        fill: '#000000'
    });
    group.add(header);

    const headerText = new Konva.Text({
        x: CONFIG.padding,
        y: 0,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        height: headerHeight,
        text: 'Jabatan ' + node.jenis_jabatan,
        fontSize: CONFIG.fontSize,
        fontFamily: 'Arial',
        fill: '#FFFFFF',
        align: 'center',
        verticalAlign: 'middle',
        fontStyle: 'bold'
    });
    group.add(headerText);

    // Line separator after header
    const headerLine = new Konva.Line({
        points: [0, headerHeight, CONFIG.boxWidth, headerHeight],
        stroke: '#000000',
        strokeWidth: 1
    });
    group.add(headerLine);

    // Position nama text
    namaText.y(headerHeight);
    group.add(namaText);

    // Line separator before kelas
    const kelasLine = new Konva.Line({
        points: [0, headerHeight + actualNamaHeight, CONFIG.boxWidth, headerHeight + actualNamaHeight],
        stroke: '#000000',
        strokeWidth: 1
    });
    group.add(kelasLine);

    // Kelas
    const kelasText = new Konva.Text({
        x: CONFIG.padding,
        y: headerHeight + actualNamaHeight,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        height: kelasHeight,
        text: node.kelas ? 'Kelas ' + node.kelas : '-',
        fontSize: CONFIG.fontSize,
        fontFamily: 'Arial',
        fill: '#000000',
        align: 'center',
        verticalAlign: 'middle'
    });
    group.add(kelasText);

    layer.add(group);
    return { width: CONFIG.boxWidth, height: totalHeight, centerX: x, bottomY: y + totalHeight };
}

// Draw table node (single column, vertical layout)
function drawTableNode(jenis, items, x, y) {
    const tableWidth = 350;
    const headerHeight = 25;
    const columnHeaderHeight = 25;
    const minRowHeight = CONFIG.tableRowHeight;

    // Calculate row heights based on nama text wrapping
    const rowHeights = items.map(item => {
        const namaText = new Konva.Text({
            width: 210,
            text: item.nama,
            fontSize: 8,
            fontFamily: 'Arial',
            wrap: 'word',
            lineHeight: 1.2
        });
        return Math.max(minRowHeight, namaText.height() + 4);
    });

    const totalHeight = headerHeight + columnHeaderHeight + rowHeights.reduce((sum, h) => sum + h, 0);

    const group = new Konva.Group({ x: x - tableWidth / 2, y: y });

    // Main border
    const border = new Konva.Rect({
        width: tableWidth,
        height: totalHeight,
        stroke: '#000000',
        strokeWidth: 1.5,
        fill: '#FFFFFF'
    });
    group.add(border);

    // Header
    const header = new Konva.Rect({
        width: tableWidth,
        height: headerHeight,
        fill: '#000000'
    });
    group.add(header);

    const headerText = new Konva.Text({
        width: tableWidth,
        height: headerHeight,
        text: 'Jabatan ' + jenis,
        fontSize: 11,
        fontFamily: 'Arial',
        fill: '#FFFFFF',
        align: 'center',
        verticalAlign: 'middle',
        fontStyle: 'bold'
    });
    group.add(headerText);

    // Column headers
    const colHeaderY = headerHeight;
    const colHeaderBg = new Konva.Rect({
        y: colHeaderY,
        width: tableWidth,
        height: columnHeaderHeight,
        fill: '#F3F4F6',
        stroke: '#000000',
        strokeWidth: 1
    });
    group.add(colHeaderBg);

    // Column widths
    const colWidths = {
        nama: 210,
        kelas: 45,
        b: 32,
        k: 32,
        s: 31
    };

    let colX = 0;
    const columns = [
        { label: 'Nama Jabatan', width: colWidths.nama },
        { label: 'Kls', width: colWidths.kelas },
        { label: 'B', width: colWidths.b },
        { label: 'K', width: colWidths.k },
        { label: 'S', width: colWidths.s }
    ];

    columns.forEach((col, idx) => {
        if (idx > 0) {
            const line = new Konva.Line({
                points: [colX, colHeaderY, colX, totalHeight],
                stroke: '#000000',
                strokeWidth: 1
            });
            group.add(line);
        }

        const colText = new Konva.Text({
            x: colX + 2,
            y: colHeaderY,
            width: col.width - 4,
            height: columnHeaderHeight,
            text: col.label,
            fontSize: 9,
            fontFamily: 'Arial',
            fill: '#000000',
            align: 'center',
            verticalAlign: 'middle',
            fontStyle: 'bold'
        });
        group.add(colText);

        colX += col.width;
    });

    // Data rows
    let currentY = headerHeight + columnHeaderHeight;
    items.forEach((item, idx) => {
        const rowHeight = rowHeights[idx];

        // Horizontal line
        const hLine = new Konva.Line({
            points: [0, currentY, tableWidth, currentY],
            stroke: '#000000',
            strokeWidth: 0.5
        });
        group.add(hLine);

        // Row data
        let cellX = 0;

        const rowData = [
            { text: item.nama, width: colWidths.nama, align: 'left', wrap: true },
            { text: item.kelas || '-', width: colWidths.kelas, align: 'center' },
            { text: item.bezetting.toString(), width: colWidths.b, align: 'center' },
            { text: item.kebutuhan.toString(), width: colWidths.k, align: 'center' },
            { text: (item.selisih >= 0 ? '+' : '') + item.selisih.toString(), width: colWidths.s, align: 'center' }
        ];

        rowData.forEach((cell) => {
            const cellText = new Konva.Text({
                x: cellX + 2,
                y: currentY + 2,
                width: cell.width - 4,
                height: rowHeight - 4,
                text: cell.text,
                fontSize: 8,
                fontFamily: 'Arial',
                fill: '#000000',
                align: cell.align,
                verticalAlign: 'middle',
                wrap: cell.wrap ? 'word' : 'none',
                lineHeight: 1.2
            });
            group.add(cellText);

            cellX += cell.width;
        });

        currentY += rowHeight;
    });

    layer.add(group);
    return { width: tableWidth, height: totalHeight, centerX: x, bottomY: y + totalHeight };
}

// Draw connector lines
function drawConnector(fromX, fromY, toX, toY) {
    if (Math.abs(fromX - toX) < 5) {
        // Straight vertical line if aligned
        const line = new Konva.Line({
            points: [fromX, fromY, toX, toY],
            stroke: '#000000',
            strokeWidth: 1.5
        });
        layer.add(line);
        line.moveToBottom();
    } else {
        // L-shaped connector
        const midY = fromY + (toY - fromY) / 2;

        const line = new Konva.Line({
            points: [
                fromX, fromY,
                fromX, midY,
                toX, midY,
                toX, toY
            ],
            stroke: '#000000',
            strokeWidth: 1.5,
            lineCap: 'square',
            lineJoin: 'miter'
        });

        layer.add(line);
        line.moveToBottom();
    }
}

// Render the tree
function renderTree(positions, parentInfo = null) {
    const strukturalPositions = [];
    const tablePositions = [];

    // Separate struktural and table positions
    positions.forEach(pos => {
        if (pos.isTable) {
            tablePositions.push(pos);
        } else {
            strukturalPositions.push(pos);
        }
    });

    // Render struktural nodes first
    strukturalPositions.forEach(pos => {
        const { node, x, y } = pos;
        const nodeInfo = drawBoxNode(node, x, y);

        // Draw connector from parent if exists
        if (parentInfo) {
            drawConnector(parentInfo.centerX, parentInfo.bottomY, x, y);
        }

        // Recursively render children (struktural children are in childPositions)
        if (node.childPositions) {
            renderTree(node.childPositions, nodeInfo);
        }
    });

    // Render table nodes - they have their own parentX/parentBottomY for connectors
    tablePositions.forEach(pos => {
        const { node, x, y, parentX, parentBottomY } = pos;
        drawTableNode(node.jenis_jabatan, node.items, x, y);

        // Draw connector from parent struktural node (not the passed parentInfo)
        if (parentX !== undefined && parentBottomY !== undefined) {
            drawConnector(parentX, parentBottomY, x, y);
        } else if (parentInfo) {
            drawConnector(parentInfo.centerX, parentInfo.bottomY, x, y);
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
