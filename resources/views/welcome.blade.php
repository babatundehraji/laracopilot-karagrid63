<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucheus - Service Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-gray-900 mb-4">Sucheus</h1>
            <p class="text-xl text-gray-600 mb-8">Service Marketplace API</p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin/login" class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Admin Panel
                </a>
                <a href="/api/health" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-heartbeat mr-2"></i>
                    API Health
                </a>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <div class="bg-white p-6 rounded-lg shadow">
                    <i class="fas fa-mobile-alt text-3xl text-blue-600 mb-3"></i>
                    <h3 class="font-semibold text-lg mb-2">Mobile API</h3>
                    <p class="text-gray-600 text-sm">RESTful API with Sanctum authentication</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <i class="fas fa-cogs text-3xl text-green-600 mb-3"></i>
                    <h3 class="font-semibold text-lg mb-2">Service Marketplace</h3>
                    <p class="text-gray-600 text-sm">Connect customers with service providers</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <i class="fas fa-chart-line text-3xl text-orange-600 mb-3"></i>
                    <h3 class="font-semibold text-lg mb-2">Admin Dashboard</h3>
                    <p class="text-gray-600 text-sm">Manage users, vendors, and services</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
