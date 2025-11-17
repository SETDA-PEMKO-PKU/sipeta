@extends('admin.layouts.app')

@section('title', 'Kelola Admin')
@section('page-title', 'Kelola Admin')

@section('content')
<div class="p-4 lg:p-8">
    <!-- Header Actions -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Daftar Administrator</h2>
            <p class="text-gray-600 mt-1">Kelola akun administrator sistem</p>
        </div>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
            <span class="iconify" data-icon="mdi:plus" data-width="18" data-height="18"></span>
            <span class="ml-2">Tambah Admin</span>
        </a>
    </div>

    <!-- Admin List -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Terdaftar</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($admins as $admin)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $admin->name }}</div>
                                        @if($admin->id === auth('admin')->id())
                                            <span class="text-xs text-primary-600">(Anda)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $admin->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($admin->isSuperAdmin())
                                    <span class="badge badge-primary">Super Admin</span>
                                @else
                                    <span class="badge badge-gray">Admin</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($admin->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-error">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $admin->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.admins.edit', $admin) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                                        <span class="iconify" data-icon="mdi:pencil" data-width="14" data-height="14"></span>
                                        <span class="ml-1">Edit</span>
                                    </a>

                                    @if($admin->id !== auth('admin')->id())
                                        <form action="{{ route('admin.admins.destroy', $admin) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                <span class="iconify" data-icon="mdi:delete" data-width="14" data-height="14"></span>
                                                <span class="ml-1">Hapus</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="iconify text-gray-300" data-icon="mdi:account-group" data-width="48" data-height="48"></span>
                                <p class="text-gray-500 mt-2">Belum ada data admin</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    <div class="mt-4 text-sm text-gray-600">
        Total: <strong class="text-gray-900">{{ $admins->count() }}</strong> administrator
    </div>
</div>
@endsection
