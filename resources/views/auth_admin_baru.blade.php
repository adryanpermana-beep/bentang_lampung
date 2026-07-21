<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Bentang Lampung</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md border border-slate-200">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-[#1e3a8a]">BENTANG LAMPUNG</h2>
            <p class="text-xs text-gray-500 tracking-wider uppercase mt-1">WebGIS & Spatial Intelligence</p>
        </div>

        <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Login Administrator</h3>

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4 border border-red-200">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Proses Kirim Data -->
        <form action="/masuk-admin" method="POST">
            <!-- Format Token Tradisional untuk Laravel 5 & PHP 5.6 -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Email Admin</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a8a] text-sm" placeholder="admin@gmail.com">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a8a] text-sm" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-[#1e3a8a] text-white py-2 px-4 rounded-lg text-sm font-semibold hover:bg-blue-900 transition-colors cursor-pointer">
                Masuk <i class="fa-solid fa-sign-in-alt ml-1"></i>
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/peta" class="text-xs text-[#1e3a8a] hover:underline">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Peta
            </a>
        </div>
    </div>

</body>
</html>