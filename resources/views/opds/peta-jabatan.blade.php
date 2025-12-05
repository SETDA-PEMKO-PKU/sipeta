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
        body {
            background: white;
        }
        .no-print {
            display: none !important;
        }
        #canvas-container {
            height: auto;
            min-height: 800px;
            page-break-inside: avoid;
        }
        .canvas-controls,
        .zoom-level {
            display: none !important;
        }
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
            <button onclick="window.print()" class="btn btn-outline">
                <span class="iconify" data-icon="mdi:printer" data-width="18" data-height="18"></span>
                <span class="ml-2">Cetak</span>
            </button>
            <button onclick="exportCanvas()" class="btn btn-primary">
                <span class="iconify" data-icon="mdi:download" data-width="18" data-height="18"></span>
                <span class="ml-2">Export PNG</span>
            </button>
        </div>
    </div>

    <!-- Organizational Chart Canvas -->
    <div class="bg-white rounded-lg shadow-sm p-6">
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
    horizontalGap: 80,
    verticalGap: 30,
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
            children: []
        };

        if (node.children && node.children.length > 0) {
            processedNode.children = processOrgData(node.children, processedNode);
        }

        processed.push(processedNode);
    });

    return processed;
}

// Group pelaksana/fungsional nodes per parent
function groupNodes(nodes) {
    const result = [];

    nodes.forEach(node => {
        if (node.jenis_jabatan === 'Pelaksana' || node.jenis_jabatan === 'Fungsional') {
            // Keep as individual node but mark as table type
            result.push({
                ...node,
                isTableNode: true
            });
        } else {
            // Struktural node - process children recursively
            const processedNode = { ...node };
            
            if (node.children && node.children.length > 0) {
                // Separate children into struktural and table nodes
                const strukturalChildren = [];
                const tableChildren = {
                    pelaksana: [],
                    fungsional: []
                };

                node.children.forEach(child => {
                    if (child.jenis_jabatan === 'Pelaksana') {
                        tableChildren.pelaksana.push(child);
                    } else if (child.jenis_jabatan === 'Fungsional') {
                        tableChildren.fungsional.push(child);
                    } else {
                        strukturalChildren.push(child);
                    }
                });

                // Recursively process struktural children
                processedNode.children = groupNodes(strukturalChildren);

                // Add table nodes as special children
                processedNode.tableChildren = {
                    pelaksana: tableChildren.pelaksana.length > 0 ? {
                        type: 'table',
                        jenis_jabatan: 'Pelaksana',
                        items: tableChildren.pelaksana
                    } : null,
                    fungsional: tableChildren.fungsional.length > 0 ? {
                        type: 'table',
                        jenis_jabatan: 'Fungsional',
                        items: tableChildren.fungsional
                    } : null
                };
            }

            result.push(processedNode);
        }
    });

    return result;
}

// Calculate tree layout
function calculateLayout(nodes, x = 0, y = 0, level = 0) {
    const positions = [];
    let totalWidth = 0;

    // Calculate width for each node
    nodes.forEach((node, index) => {
        let nodeWidth = CONFIG.boxWidth;

        // Calculate child layout if has children
        if (node.children && node.children.length > 0) {
            const childLayout = calculateLayout(node.children, 0, 0, level + 1);
            node.childLayout = childLayout;
            nodeWidth = Math.max(nodeWidth, childLayout.totalWidth);
        }

        // Calculate table children width
        if (node.tableChildren) {
            const TABLE_WIDTH = 280;
            const tableGap = 40;
            let tableWidth = 0;

            if (node.tableChildren.pelaksana && node.tableChildren.fungsional) {
                tableWidth = TABLE_WIDTH * 2 + tableGap;
            } else if (node.tableChildren.pelaksana || node.tableChildren.fungsional) {
                tableWidth = TABLE_WIDTH;
            }

            nodeWidth = Math.max(nodeWidth, tableWidth);
        }

        node.layoutWidth = nodeWidth;
        totalWidth += nodeWidth;

        if (index > 0) {
            totalWidth += CONFIG.horizontalGap;
        }
    });

    // Position nodes horizontally
    let currentX = x - totalWidth / 2;

    nodes.forEach((node, index) => {
        if (index > 0) {
            currentX += CONFIG.horizontalGap;
        }

        const nodeX = currentX + node.layoutWidth / 2;
        const nodeY = y;

        positions.push({
            node: node,
            x: nodeX,
            y: nodeY
        });

        // Calculate next level Y position
        let nextY = y + CONFIG.verticalGap + CONFIG.boxHeight;

        // Position table children first (they appear before struktural children)
        if (node.tableChildren) {
            const TABLE_WIDTH = 280;
            const tableGap = 40;
            const hasBoth = node.tableChildren.pelaksana && node.tableChildren.fungsional;

            if (hasBoth) {
                // Position side by side
                const totalTableWidth = TABLE_WIDTH * 2 + tableGap;
                const startX = nodeX - totalTableWidth / 2;

                if (node.tableChildren.pelaksana) {
                    positions.push({
                        node: node.tableChildren.pelaksana,
                        x: startX + TABLE_WIDTH / 2,
                        y: nextY,
                        isTable: true,
                        parentX: nodeX,
                        parentY: y + CONFIG.boxHeight
                    });
                }

                if (node.tableChildren.fungsional) {
                    positions.push({
                        node: node.tableChildren.fungsional,
                        x: startX + TABLE_WIDTH + tableGap + TABLE_WIDTH / 2,
                        y: nextY,
                        isTable: true,
                        parentX: nodeX,
                        parentY: y + CONFIG.boxHeight
                    });
                }

                // Calculate max table height for next level
                const pelaksanaHeight = node.tableChildren.pelaksana ? 
                    (45 + node.tableChildren.pelaksana.items.length * CONFIG.tableRowHeight) : 0;
                const fungsionalHeight = node.tableChildren.fungsional ? 
                    (45 + node.tableChildren.fungsional.items.length * CONFIG.tableRowHeight) : 0;
                const maxTableHeight = Math.max(pelaksanaHeight, fungsionalHeight);

                nextY += maxTableHeight + CONFIG.verticalGap;
            } else {
                // Single table - center it
                const table = node.tableChildren.pelaksana || node.tableChildren.fungsional;
                
                positions.push({
                    node: table,
                    x: nodeX,
                    y: nextY,
                    isTable: true,
                    parentX: nodeX,
                    parentY: y + CONFIG.boxHeight
                });

                const tableHeight = 45 + table.items.length * CONFIG.tableRowHeight;
                nextY += tableHeight + CONFIG.verticalGap;
            }
        }

        // Position struktural children
        if (node.childLayout) {
            node.childPositions = calculateLayout(node.children, nodeX, nextY, level + 1).positions;
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
    const namaHeight = 30;
    const kelasHeight = 25;
    const totalHeight = headerHeight + namaHeight + kelasHeight;
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

    // Nama Jabatan
    const namaText = new Konva.Text({
        x: CONFIG.padding,
        y: headerHeight,
        width: CONFIG.boxWidth - CONFIG.padding * 2,
        height: namaHeight,
        text: node.nama,
        fontSize: CONFIG.fontSize,
        fontFamily: 'Arial',
        fill: '#000000',
        align: 'center',
        verticalAlign: 'middle'
    });
    group.add(namaText);

    // Line separator before kelas
    const kelasLine = new Konva.Line({
        points: [0, headerHeight + namaHeight, CONFIG.boxWidth, headerHeight + namaHeight],
        stroke: '#000000',
        strokeWidth: 1
    });
    group.add(kelasLine);

    // Kelas
    const kelasText = new Konva.Text({
        x: CONFIG.padding,
        y: headerHeight + namaHeight,
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
    const tableWidth = 280;
    const headerHeight = 25;
    const columnHeaderHeight = 25;
    const rowHeight = CONFIG.tableRowHeight;
    const totalHeight = headerHeight + columnHeaderHeight + (items.length * rowHeight);

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
        nama: 155,
        kelas: 45,
        b: 27,
        k: 27,
        s: 26
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
    items.forEach((item, idx) => {
        const rowY = headerHeight + columnHeaderHeight + (idx * rowHeight);

        // Horizontal line
        const hLine = new Konva.Line({
            points: [0, rowY, tableWidth, rowY],
            stroke: '#000000',
            strokeWidth: 0.5
        });
        group.add(hLine);

        // Row data
        let cellX = 0;

        // Truncate nama if too long
        const namaText = item.nama.length > 28 ? item.nama.substring(0, 25) + '...' : item.nama;

        const rowData = [
            { text: namaText, width: colWidths.nama, align: 'left' },
            { text: item.kelas || '-', width: colWidths.kelas, align: 'center' },
            { text: item.bezetting.toString(), width: colWidths.b, align: 'center' },
            { text: item.kebutuhan.toString(), width: colWidths.k, align: 'center' },
            { text: (item.selisih >= 0 ? '+' : '') + item.selisih.toString(), width: colWidths.s, align: 'center' }
        ];

        rowData.forEach((cell) => {
            const cellText = new Konva.Text({
                x: cellX + 2,
                y: rowY + 1,
                width: cell.width - 4,
                height: rowHeight - 2,
                text: cell.text,
                fontSize: 8,
                fontFamily: 'Arial',
                fill: '#000000',
                align: cell.align,
                verticalAlign: 'middle'
            });
            group.add(cellText);

            cellX += cell.width;
        });
    });

    layer.add(group);
    return { width: tableWidth, height: totalHeight, centerX: x, bottomY: y + totalHeight };
}

// Draw connector lines (straight line from parent to child)
function drawConnector(fromX, fromY, toX, toY) {
    // Draw a simple straight vertical line if they're aligned
    if (Math.abs(fromX - toX) < 5) {
        const line = new Konva.Line({
            points: [fromX, fromY, toX, toY],
            stroke: '#000000',
            strokeWidth: 1.5
        });
        layer.add(line);
        line.moveToBottom();
    } else {
        // Draw L-shaped connector if not aligned
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
    positions.forEach(pos => {
        if (pos.isTable) {
            // Render table node
            const { node, x, y, parentX, parentY } = pos;
            drawTableNode(node.jenis_jabatan, node.items, x, y);

            // Draw connector from parent
            if (parentX !== undefined && parentY !== undefined) {
                drawConnector(parentX, parentY, x, y);
            }
        } else {
            // Render struktural node
            const { node, x, y } = pos;
            const nodeInfo = drawBoxNode(node, x, y);

            // Draw connector from parent if exists
            if (parentInfo) {
                drawConnector(parentInfo.centerX, parentInfo.bottomY, x, y);
            }

            // Recursively render children
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

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    if (orgData && orgData.length > 0) {
        initStage();

        const processedData = processOrgData(orgData);
        const groupedData = groupNodes(processedData);
        const layout = calculateLayout(groupedData, stage.width() / 2, 50, 0);

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
