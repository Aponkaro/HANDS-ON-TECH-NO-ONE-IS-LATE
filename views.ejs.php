<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hands On Tech - Smart Printing Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-gray-800">

    <header class="bg-red-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-print text-3xl text-yellow-400"></i>
                <div>
                    <h1 class="text-2xl font-bold leading-none">Hands On Tech</h1>
                    <p class="text-xs text-yellow-300 mt-1">Smart Printing Management System | Contact: 0246116269</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button onclick="toggleModal('jobModal')" class="bg-yellow-500 hover:bg-yellow-600 text-slate-900 font-bold px-4 py-2 rounded-lg text-sm transition">
                    + New Print Job
                </button>
                <button onclick="toggleModal('customerModal')" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
                    + Add Customer
                </button>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-yellow-500 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase">Pending Jobs</p>
                    <p class="text-2xl font-bold text-gray-800"><%= metrics.pending_count %></p>
                </div>
                <i class="fa-solid fa-clock text-yellow-500 text-3xl"></i>
            </div>
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-blue-500 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase">In Progress</p>
                    <p class="text-2xl font-bold text-gray-800"><%= metrics.progress_count %></p>
                </div>
                <i class="fa-solid fa-spinner text-blue-500 text-3xl"></i>
            </div>
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-green-500 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase">Completed</p>
                    <p class="text-2xl font-bold text-gray-800"><%= metrics.completed_count %></p>
                </div>
                <i class="fa-solid fa-circle-check text-green-500 text-3xl"></i>
            </div>
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-red-700 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-semibold uppercase">Total Revenue Paid</p>
                    <p class="text-2xl font-bold text-gray-800">GH₵ <%= Number(metrics.total_revenue).toFixed(2) %></p>
                </div>
                <i class="fa-solid fa-money-bill-wave text-red-700 text-3xl"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white rounded-xl shadow p-5">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-red-800"></i> Active Job Board
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="p-3">Job Title</th>
                                <th class="p-3">Customer</th>
                                <th class="p-3">Total</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <% if (jobs.length === 0) { %>
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-400">No print jobs found. Click "+ New Print Job" to create one.</td>
                                </tr>
                            <% } %>
                            <% jobs.forEach(job => { %>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-semibold text-gray-800"><%= job.job_title %></td>
                                <td class="p-3">
                                    <%= job.customer_name %><br>
                                    <span class="text-xs text-gray-400"><%= job.phone %></span>
                                </td>
                                <td class="p-3 font-medium text-gray-800">GH₵ <%= Number(job.total_price).toFixed(2) %></td>
                                <td class="p-3">
                                    <form action="/update-job-status" method="POST" class="inline">
                                        <input type="hidden" name="job_id" value="<%= job.id %>">
                                        <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded px-2 py-1 border border-gray-200 cursor-pointer
                                            <%= job.status === 'completed' ? 'bg-green-100 text-green-800' : '' %>
                                            <%= job.status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' %>
                                            <%= job.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' %>
                                            <%= job.status === 'cancelled' ? 'bg-red-100 text-red-800' : '' %>">
                                            <option value="pending" <%= job.status === 'pending' ? 'selected' : '' %>>PENDING</option>
                                            <option value="in_progress" <%= job.status === 'in_progress' ? 'selected' : '' %>>IN PROGRESS</option>
                                            <option value="completed" <%= job.status === 'completed' ? 'selected' : '' %>>COMPLETED</option>
                                            <option value="cancelled" <%= job.status === 'cancelled' ? 'selected' : '' %>>CANCELLED</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="p-3">
                                    <a href="/invoice/<%= job.id %>" target="_blank" class="text-red-800 hover:text-red-600 font-bold text-xs">
                                        <i class="fa-solid fa-file-invoice"></i> Invoice
                                    </a>
                                </td>
                            </tr>
                            <% }) %>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-5 border border-gray-100 h-fit">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-red-800"></i> Instant Price Calculator
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Printing Category</label>
                        <select id="calc_type" onchange="calculatePrice()" class="w-full border rounded-lg p-2 text-sm">
                            <option value="5">A4 Flyer / Document - GH₵ 5.00</option>
                            <option value="15">Large Banner (per sq meter) - GH₵ 15.00</option>
                            <option value="2">Business Card - GH₵ 2.00</option>
                            <option value="50">T-Shirt Printing - GH₵ 50.00</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Quantity</label>
                        <input type="number" id="calc_qty" value="1" min="1" oninput="calculatePrice()" class="w-full border rounded-lg p-2 text-sm">
                    </div>
                    <div class="p-4 bg-red-50 rounded-lg text-center border border-red-100">
                        <span class="text-xs text-gray-600 block uppercase font-semibold">Estimated Total Cost</span>
                        <span id="calc_total" class="text-2xl font-extrabold text-red-800">GH₵ 5.00</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Job -->
    <div id="jobModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Create New Print Job</h3>
            <form action="/create-job" method="POST" class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Job Title / Description</label>
                    <input type="text" name="job_title" placeholder="e.g. 1000 Event Flyers" required class="w-full border rounded-lg p-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Select Customer</label>
                    <select name="customer_id" required class="w-full border rounded-lg p-2 text-sm">
                        <% customers.forEach(c => { %>
                            <option value="<%= c.id %>"><%= c.name %> (<%= c.phone %>)</option>
                        <% }) %>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Quantity</label>
                        <input type="number" name="quantity" required value="1" min="1" class="w-full border rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Unit Price (GH₵)</label>
                        <input type="number" step="0.01" name="unit_price" required placeholder="0.00" class="w-full border rounded-lg p-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Amount Paid (GH₵)</label>
                    <input type="number" step="0.01" name="amount_paid" value="0.00" class="w-full border rounded-lg p-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Job Status</label>
                    <select name="status" class="w-full border rounded-lg p-2 text-sm">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-2 border-t">
                    <button type="button" onclick="toggleModal('jobModal')" class="px-4 py-2 text-gray-500 text-sm font-semibold">Cancel</button>
                    <button type="submit" class="bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-900">Save Job</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Customer -->
    <div id="customerModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Add New Customer</h3>
            <form action="/add-customer" method="POST" class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full border rounded-lg p-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Phone Number</label>
                    <input type="text" name="phone" required class="w-full border rounded-lg p-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Email Address (Optional)</label>
                    <input type="email" name="email" class="w-full border rounded-lg p-2 text-sm">
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-2 border-t">
                    <button type="button" onclick="toggleModal('customerModal')" class="px-4 py-2 text-gray-500 text-sm font-semibold">Cancel</button>
                    <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-900">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(id) { document.getElementById(id).classList.toggle('hidden'); }
        function calculatePrice() {
            const unit = parseFloat(document.getElementById('calc_type').value) || 0;
            const qty = parseInt(document.getElementById('calc_qty').value) || 0;
            document.getElementById('calc_total').innerText = 'GH₵ ' + (unit * qty).toFixed(2);
        }
    </script>
</body>
</html>