<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AKUPETA - Aplikasi Kendali Utama Peta Jabatan untuk manajemen struktur organisasi dan kepegawaian">
    <title>AKUPETA - Aplikasi Kendali Utama Peta Jabatan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
</head>
<body class="antialiased bg-white">
    <!-- Navigation -->
    <nav class="fixed w-full bg-white/95 backdrop-blur-sm shadow-sm z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-600 to-primary-700 rounded-lg flex items-center justify-center shadow-lg">
                        <span class="iconify text-white" data-icon="mdi:sitemap" data-width="24" data-height="24"></span>
                    </div>
                    <span class="text-xl font-bold text-gray-900">AKUPETA</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-gray-700 hover:text-primary-600 transition-colors font-medium">Fitur</a>
                    <a href="#benefits" class="text-gray-700 hover:text-primary-600 transition-colors font-medium">Keunggulan</a>
                    <a href="#about" class="text-gray-700 hover:text-primary-600 transition-colors font-medium">Tentang</a>
                    <a href="{{ route('admin.login') }}" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:login" data-width="18" data-height="18"></span>
                        Login
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700">
                    <span class="iconify" data-icon="mdi:menu" data-width="24" data-height="24"></span>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="md:hidden pb-4"
                 style="display: none;">
                <div class="flex flex-col gap-3 pt-2">
                    <a href="#features" class="text-gray-700 hover:text-primary-600 transition-colors font-medium px-2 py-2">Fitur</a>
                    <a href="#benefits" class="text-gray-700 hover:text-primary-600 transition-colors font-medium px-2 py-2">Keunggulan</a>
                    <a href="#about" class="text-gray-700 hover:text-primary-600 transition-colors font-medium px-2 py-2">Tentang</a>
                    <a href="{{ route('admin.login') }}" class="btn btn-primary w-full">
                        <span class="iconify" data-icon="mdi:login" data-width="18" data-height="18"></span>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-24 pb-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-primary-50 via-white to-primary-50">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-medium">
                        <span class="iconify" data-icon="mdi:shield-check" data-width="16" data-height="16"></span>
                        Sistem Kendali Kepegawaian
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                        Kelola Struktur Organisasi dengan
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-primary-800">AKUPETA</span>
                    </h1>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        Aplikasi Kendali Utama Peta Jabatan untuk pengelolaan struktur organisasi, jabatan, dan kepegawaian di lingkungan OPD secara terkendali, efisien, dan terstruktur.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('admin.login') }}" class="btn btn-primary btn-lg">
                            <span class="iconify" data-icon="mdi:login" data-width="20" data-height="20"></span>
                            Masuk ke Sistem
                        </a>
                        <a href="#features" class="btn btn-outline btn-lg">
                            <span class="iconify" data-icon="mdi:information-outline" data-width="20" data-height="20"></span>
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-400 to-primary-600 rounded-3xl blur-3xl opacity-20"></div>
                    <div class="relative bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                        <div class="space-y-6">
                            <!-- Mock Organization Chart -->
                            <div class="flex items-center justify-center">
                                <div class="bg-gradient-to-br from-primary-500 to-primary-600 text-white px-6 py-3 rounded-xl shadow-lg">
                                    <div class="text-sm font-semibold">Kepala Dinas</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-4">
                                <div class="h-12 w-0.5 bg-gray-300"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium text-center">
                                    Sekretaris
                                </div>
                                <div class="bg-green-100 text-green-700 px-3 py-2 rounded-lg text-xs font-medium text-center">
                                    Bidang 1
                                </div>
                                <div class="bg-purple-100 text-purple-700 px-3 py-2 rounded-lg text-xs font-medium text-center">
                                    Bidang 2
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <span class="iconify text-primary-600" data-icon="mdi:account-group" data-width="18" data-height="18"></span>
                                        <span>Data Pegawai</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <span class="iconify text-green-600" data-icon="mdi:briefcase" data-width="18" data-height="18"></span>
                                        <span>Struktur Jabatan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary-600 mb-2">100%</div>
                    <div class="text-gray-600 font-medium">Digital</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary-600 mb-2">Terkendali</div>
                    <div class="text-gray-600 font-medium">Transparan</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary-600 mb-2">Mudah</div>
                    <div class="text-gray-600 font-medium">User-Friendly</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary-600 mb-2">Aman</div>
                    <div class="text-gray-600 font-medium">Terpercaya</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Fitur Unggulan</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Kelola seluruh aspek kepegawaian dan struktur organisasi dengan fitur lengkap dan terintegrasi
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="iconify text-blue-600" data-icon="mdi:sitemap" data-width="28" data-height="28"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Peta Jabatan Visual</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Visualisasi struktur organisasi dalam bentuk diagram hierarki yang mudah dipahami dan interaktif
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="iconify text-green-600" data-icon="mdi:account-multiple" data-width="28" data-height="28"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Manajemen Pegawai</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Kelola data pegawai ASN secara komprehensif dengan informasi lengkap dan terstruktur
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="iconify text-purple-600" data-icon="mdi:office-building" data-width="28" data-height="28"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Multi OPD</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Mendukung pengelolaan berbagai OPD dalam satu sistem yang terintegrasi
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="iconify text-orange-600" data-icon="mdi:chart-line" data-width="28" data-height="28"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Analisis & Laporan</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dashboard analitik lengkap dengan visualisasi data dan laporan yang dapat diekspor
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="iconify text-red-600" data-icon="mdi:chart-box" data-width="28" data-height="28"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Gap Analysis</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Analisis kebutuhan pegawai dan identifikasi kekurangan di setiap jabatan
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="iconify text-indigo-600" data-icon="mdi:file-export" data-width="28" data-height="28"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Export Data</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Ekspor data dan laporan dalam format Excel dan PDF untuk dokumentasi
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Mengapa AKUPETA?</h2>
                        <p class="text-lg text-gray-600">
                            Solusi terbaik untuk meningkatkan kontrol dan transparansi pengelolaan kepegawaian
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="24" data-height="24"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Kendali Penuh</h3>
                                <p class="text-gray-600">Sistem kontrol lengkap untuk memastikan pengelolaan data dan struktur organisasi yang terkendali</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span class="iconify text-blue-600" data-icon="mdi:clock-fast" data-width="24" data-height="24"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Efisiensi Waktu</h3>
                                <p class="text-gray-600">Proses pengelolaan data yang lebih cepat dan otomatis, menghemat waktu kerja</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <span class="iconify text-purple-600" data-icon="mdi:chart-timeline-variant" data-width="24" data-height="24"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Laporan Real-time</h3>
                                <p class="text-gray-600">Dashboard dan laporan yang selalu terupdate secara otomatis dan real-time</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <span class="iconify text-orange-600" data-icon="mdi:shield-lock" data-width="24" data-height="24"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Keamanan Data</h3>
                                <p class="text-gray-600">Sistem keamanan berlapis dengan kontrol akses yang ketat untuk melindungi data</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-200 to-primary-300 rounded-3xl blur-3xl opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-primary-500 to-primary-700 rounded-3xl p-8 text-white shadow-2xl">
                        <div class="space-y-6">
                            <h3 class="text-2xl font-bold mb-6">Modul Sistem</h3>

                            <div class="space-y-4">
                                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <span class="iconify" data-icon="mdi:view-dashboard" data-width="24" data-height="24"></span>
                                    <span class="font-medium">Dashboard Analytics</span>
                                </div>

                                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <span class="iconify" data-icon="mdi:office-building" data-width="24" data-height="24"></span>
                                    <span class="font-medium">Manajemen OPD</span>
                                </div>

                                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <span class="iconify" data-icon="mdi:briefcase" data-width="24" data-height="24"></span>
                                    <span class="font-medium">Struktur Jabatan</span>
                                </div>

                                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <span class="iconify" data-icon="mdi:account-group" data-width="24" data-height="24"></span>
                                    <span class="font-medium">Database Pegawai ASN</span>
                                </div>

                                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <span class="iconify" data-icon="mdi:file-document-multiple" data-width="24" data-height="24"></span>
                                    <span class="font-medium">Laporan & Export</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Tentang AKUPETA</h2>
                <p class="text-lg text-gray-600 leading-relaxed mb-8">
                    AKUPETA (Aplikasi Kendali Utama Peta Jabatan) adalah sistem informasi yang dirancang untuk meningkatkan kontrol dan transparansi dalam pengelolaan struktur organisasi dan kepegawaian di lingkungan Organisasi Perangkat Daerah (OPD).
                </p>
                <p class="text-lg text-gray-600 leading-relaxed mb-8">
                    Dengan fitur visualisasi hierarki organisasi, manajemen data pegawai ASN, analisis kebutuhan SDM, serta dashboard analitik yang komprehensif, AKUPETA membantu meningkatkan efisiensi dan efektivitas pengelolaan kepegawaian secara terkendali.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm">
                        <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                        <span class="text-gray-700 font-medium">Berbasis Web</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm">
                        <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                        <span class="text-gray-700 font-medium">Terkendali</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm">
                        <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                        <span class="text-gray-700 font-medium">Terintegrasi</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm">
                        <span class="iconify text-green-600" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                        <span class="text-gray-700 font-medium">Transparan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Kendalikan Pengelolaan Kepegawaian Anda
            </h2>
            <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
                Mulai kelola struktur organisasi dan kepegawaian dengan lebih terkendali dan efisien menggunakan AKUPETA
            </p>
            <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 bg-white text-primary-700 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-50 transition-colors shadow-xl">
                <span class="iconify" data-icon="mdi:login" data-width="24" data-height="24"></span>
                Masuk ke Sistem
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-600 to-primary-700 rounded-lg flex items-center justify-center">
                            <span class="iconify text-white" data-icon="mdi:sitemap" data-width="24" data-height="24"></span>
                        </div>
                        <span class="text-xl font-bold text-white">AKUPETA</span>
                    </div>
                    <p class="text-gray-400 leading-relaxed">
                        Aplikasi Kendali Utama Peta Jabatan untuk manajemen struktur organisasi dan kepegawaian yang terkendali.
                    </p>
                </div>

                <div>
                    <h3 class="text-white font-bold mb-4">Menu</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:text-primary-400 transition-colors">Fitur</a></li>
                        <li><a href="#benefits" class="hover:text-primary-400 transition-colors">Keunggulan</a></li>
                        <li><a href="#about" class="hover:text-primary-400 transition-colors">Tentang</a></li>
                        <li><a href="{{ route('admin.login') }}" class="hover:text-primary-400 transition-colors">Login</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white font-bold mb-4">Informasi</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2">
                            <span class="iconify text-primary-400" data-icon="mdi:map-marker" data-width="18" data-height="18"></span>
                            <span>Sistem Internal OPD</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="iconify text-primary-400" data-icon="mdi:shield-check" data-width="18" data-height="18"></span>
                            <span>Terkendali & Transparan</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} AKUPETA - Aplikasi Kendali Utama Peta Jabatan. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
