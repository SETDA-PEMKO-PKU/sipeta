@extends('admin.layouts.app')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.admins.index') }}" class="text-gray-600 hover:text-gray-900">
                <span class="iconify" data-icon="mdi:arrow-left" data-width="20" data-height="20"></span>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Edit Administrator</h2>
        </div>
        <p class="text-gray-600">Update informasi administrator</p>
    </div>

    <!-- Form -->
    <div class="max-w-2xl">
        <div class="card">
            <form action="{{ route('admin.admins.update', $admin) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $admin->name) }}"
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
                           value="{{ old('email', $admin->email) }}"
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
                        Password Baru
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="input w-full @error('password') border-red-300 @enderror"
                           placeholder="Kosongkan jika tidak ingin mengubah password">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah.</p>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password Baru
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="input w-full"
                           placeholder="Ulangi password baru">
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
                        <option value="super_admin" {{ old('role', $admin->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin_organisasi" {{ old('role', $admin->role) === 'admin_organisasi' ? 'selected' : '' }}>Admin Organisasi</option>
                        <option value="admin_bkpsdm" {{ old('role', $admin->role) === 'admin_bkpsdm' ? 'selected' : '' }}>Admin BKPSDM</option>
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
                               {{ old('is_active', $admin->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Aktifkan akun</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Admin yang tidak aktif tidak dapat login</p>
                </div>

                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <span class="iconify text-blue-600 flex-shrink-0" data-icon="mdi:information" data-width="20" data-height="20"></span>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Informasi:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>Terdaftar pada: {{ $admin->created_at->format('d M Y, H:i') }}</li>
                                <li>Terakhir diupdate: {{ $admin->updated_at->format('d M Y, H:i') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:content-save" data-width="18" data-height="18"></span>
                        <span class="ml-2">Update</span>
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
