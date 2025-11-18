@extends('admin.layouts.app')

@section('title', 'Tambah Admin')
@section('page-title', 'Tambah Admin')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.admins.index') }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Tambah Administrator Baru</h2>
        </div>
        <p class="text-gray-600">Lengkapi form untuk menambahkan administrator baru</p>
    </div>

    <!-- Form -->
    <div class="max-w-2xl">
        <div class="card">
            <form action="{{ route('admin.admins.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name') }}"
                           required
                           class="input w-full @error('name') border-red-300 @enderror"
                           placeholder="Masukkan nama lengkap">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           class="input w-full @error('email') border-red-300 @enderror"
                           placeholder="admin@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           class="input w-full @error('password') border-red-300 @enderror"
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           required
                           class="input w-full"
                           placeholder="Ulangi password">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role"
                            id="role"
                            required
                            class="input w-full @error('role') border-red-300 @enderror">
                        <option value="">Pilih Role</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin_organisasi" {{ old('role') === 'admin_organisasi' ? 'selected' : '' }}>Admin Organisasi</option>
                        <option value="admin_bkpsdm" {{ old('role') === 'admin_bkpsdm' ? 'selected' : '' }}>Admin BKPSDM</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 text-xs text-gray-600 space-y-1">
                        <p><strong>Super Admin:</strong> Akses penuh ke semua fitur</p>
                        <p><strong>Admin Organisasi:</strong> Tidak bisa tambah ASN</p>
                        <p><strong>Admin BKPSDM:</strong> Tidak bisa kelola OPD dan Jabatan</p>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Aktifkan akun</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Admin yang tidak aktif tidak dapat login</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:content-save" data-width="18" data-height="18"></span>
                        <span class="ml-2">Simpan</span>
                    </button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
