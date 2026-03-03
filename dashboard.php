<?php
/**
 * Dashboard API Endpoint
 * Provides JSON data for AJAX updates and real-time dashboard statistics
 */
require_once 'php_action/db_connect.php'; // Adjust path as needed

if (isset($_GET['request'])) {
	header('Content-Type: application/json');

	function sanitize_input($data)
	{
		return htmlspecialchars(strip_tags(trim($data)));
	}

	$request = isset($_GET['request']) ? sanitize_input($_GET['request']) : '';
	$response = ['success' => false, 'data' => null, 'message' => ''];

	try {
		switch ($request) {
			case 'stats':
				$today = date('Y-m-d');
				$currentMonth = date('Y-m');
				$lastMonth = date('Y-m', strtotime('-1 month'));

				$stats = [];

				// Total Products
				$sql = "SELECT COUNT(*) as count FROM product WHERE status = 1";
				$result = $connect->query($sql);
				$stats['totalProducts'] = $result->fetch_assoc()['count'];

				// Active Orders
				$sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 1";
				$result = $connect->query($sql);
				$stats['activeOrders'] = $result->fetch_assoc()['count'];

				// Today's Revenue
				$stmt = $connect->prepare("SELECT COALESCE(SUM(CAST(grand_total AS DECIMAL(10,2))), 0) as total FROM orders WHERE order_date = ?");
				$stmt->bind_param("s", $today);
				$stmt->execute();
				$result = $stmt->get_result();
				$stats['todayRevenue'] = $result->fetch_assoc()['total'];
				$stmt->close();

				// Month Revenue
				$stmt = $connect->prepare("SELECT COALESCE(SUM(CAST(grand_total AS DECIMAL(10,2))), 0) as total FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = ?");
				$stmt->bind_param("s", $currentMonth);
				$stmt->execute();
				$result = $stmt->get_result();
				$stats['monthRevenue'] = $result->fetch_assoc()['total'];
				$stmt->close();

				// Last Month Revenue
				$stmt = $connect->prepare("SELECT COALESCE(SUM(CAST(grand_total AS DECIMAL(10,2))), 0) as total FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = ?");
				$stmt->bind_param("s", $lastMonth);
				$stmt->execute();
				$result = $stmt->get_result();
				$lastMonthRevenue = $result->fetch_assoc()['total'];
				$stmt->close();

				// Calculate growth
				$stats['revenueGrowth'] = 0;
				if ($lastMonthRevenue > 0) {
					$stats['revenueGrowth'] = (($stats['monthRevenue'] - $lastMonthRevenue) / $lastMonthRevenue) * 100;
				}

				// Low Stock
				$sql = "SELECT COUNT(*) as count FROM product WHERE quantity <= 3 AND status = 1";
				$result = $connect->query($sql);
				$stats['lowStock'] = $result->fetch_assoc()['count'];

				// Total Customers
				$sql = "SELECT COUNT(DISTINCT client_name) as count FROM orders WHERE client_name != '_'";
				$result = $connect->query($sql);
				$stats['totalCustomers'] = $result->fetch_assoc()['count'];

				$response['success'] = true;
				$response['data'] = $stats;
				break;

			case 'revenue_chart':
				$days = isset($_GET['days']) ? intval($_GET['days']) : 7;
				$days = min($days, 30);

				$chartData = ['labels' => [], 'values' => []];

				for ($i = $days - 1; $i >= 0; $i--) {
					$date = date('Y-m-d', strtotime("-$i days"));
					$chartData['labels'][] = date('M d', strtotime($date));

					$stmt = $connect->prepare("SELECT COALESCE(SUM(CAST(grand_total AS DECIMAL(10,2))), 0) as total FROM orders WHERE order_date = ?");
					$stmt->bind_param("s", $date);
					$stmt->execute();
					$result = $stmt->get_result();
					$chartData['values'][] = floatval($result->fetch_assoc()['total']);
					$stmt->close();
				}

				$response['success'] = true;
				$response['data'] = $chartData;
				break;

			default:
				$response['message'] = 'Invalid request type';
				break;
		}
	} catch (Exception $e) {
		$response['success'] = false;
		$response['message'] = 'Error: ' . $e->getMessage();
	}

	if (isset($connect)) {
		$connect->close();
	}

	echo json_encode($response);
	exit;
}
?>

<?php require_once 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
	}

	:root {
		--primary: #6366f1;
		--primary-dark: #4f46e5;
		--secondary: #0ea5e9;
		--success: #10b981;
		--warning: #f59e0b;
		--danger: #ef4444;
		--dark: #1f2937;
		--gray: #6b7280;
		--light-gray: #f3f4f6;
		--white: #ffffff;
		--shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
		--shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
	}

	body {
		font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		min-height: 100vh;
		padding: 20px;
	}

	.container {
		max-width: 1600px;
		margin: 0 auto;
	}

	.header {
		background: var(--white);
		border-radius: 20px;
		padding: 32px;
		margin-bottom: 24px;
		box-shadow: var(--shadow);
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-wrap: wrap;
		gap: 16px;
	}

	.header-content h1 {
		color: var(--dark);
		font-size: 32px;
		font-weight: 700;
		margin-bottom: 8px;
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.header-content p {
		color: var(--gray);
		font-size: 16px;
	}

	.header-actions {
		display: flex;
		gap: 12px;
	}

	.btn {
		padding: 12px 24px;
		border-radius: 12px;
		border: none;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
		font-size: 14px;
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}

	.btn-primary {
		background: var(--primary);
		color: white;
	}

	.btn-primary:hover {
		background: var(--primary-dark);
		transform: translateY(-2px);
		box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
	}

	.btn-outline {
		background: transparent;
		border: 2px solid var(--primary);
		color: var(--primary);
	}

	.btn-outline:hover {
		background: var(--primary);
		color: white;
	}

	.stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 24px;
		margin-bottom: 32px;
	}

	.stat-card {
		background: var(--white);
		border-radius: 20px;
		padding: 28px;
		box-shadow: var(--shadow);
		transition: all 0.3s ease;
		position: relative;
		overflow: hidden;
	}

	.stat-card::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 4px;
		background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
	}

	.stat-card:hover {
		transform: translateY(-8px);
		box-shadow: var(--shadow-lg);
	}

	.stat-card.success::before {
		background: linear-gradient(90deg, var(--success) 0%, #059669 100%);
	}

	.stat-card.warning::before {
		background: linear-gradient(90deg, var(--warning) 0%, #d97706 100%);
	}

	.stat-card.danger::before {
		background: linear-gradient(90deg, var(--danger) 0%, #dc2626 100%);
	}

	.stat-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		margin-bottom: 20px;
	}

	.stat-info h3 {
		color: var(--gray);
		font-size: 14px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin-bottom: 8px;
	}

	.stat-value {
		font-size: 36px;
		font-weight: 800;
		color: var(--dark);
		line-height: 1;
	}

	.stat-icon {
		width: 64px;
		height: 64px;
		border-radius: 16px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 28px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	}

	.stat-icon.primary {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	}

	.stat-icon.success {
		background: linear-gradient(135deg, #10b981 0%, #059669 100%);
	}

	.stat-icon.warning {
		background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
	}

	.stat-icon.danger {
		background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
	}

	.stat-icon.info {
		background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
	}

	.stat-footer {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-top: 16px;
		padding-top: 16px;
		border-top: 2px solid var(--light-gray);
	}

	.stat-change {
		display: flex;
		align-items: center;
		gap: 4px;
		font-size: 14px;
		font-weight: 600;
	}

	.stat-change.up {
		color: var(--success);
	}

	.stat-change.down {
		color: var(--danger);
	}

	.charts-section {
		display: grid;
		grid-template-columns: 1fr;
		gap: 24px;
	}

	.chart-card {
		background: var(--white);
		border-radius: 20px;
		padding: 28px;
		box-shadow: var(--shadow);
	}

	.chart-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 24px;
		padding-bottom: 20px;
		border-bottom: 2px solid var(--light-gray);
	}

	.chart-header h3 {
		color: var(--dark);
		font-size: 20px;
		font-weight: 700;
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.chart-filters {
		display: flex;
		gap: 8px;
	}

	.filter-btn {
		padding: 8px 16px;
		border-radius: 8px;
		border: 2px solid var(--light-gray);
		background: transparent;
		color: var(--gray);
		font-size: 13px;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.filter-btn.active {
		background: var(--primary);
		color: white;
		border-color: var(--primary);
	}

	.chart-container {
		position: relative;
		height: 400px;
	}

	@media (max-width: 1200px) {
		.charts-section {
			grid-template-columns: 1fr;
		}
	}

	@media (max-width: 768px) {
		.header {
			flex-direction: column;
			align-items: flex-start;
		}

		.stats-grid {
			grid-template-columns: 1fr;
		}

		.header-content h1 {
			font-size: 24px;
		}

		.stat-value {
			font-size: 28px;
		}
	}

	@keyframes fadeIn {
		from {
			opacity: 0;
			transform: translateY(20px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.animate {
		animation: fadeIn 0.5s ease-out;
	}
</style>


<div class="container">
	<!-- Header -->
	<div class="header animate">
		<div class="header-content">
			<h1>
				<span>📊</span>
				Dashboard Overview
			</h1>
			<p id="currentDateTime">Loading...</p>
		</div>
		<div class="header-actions">
			<button class="btn btn-outline" onclick="refreshDashboard()">
				<span>🔄</span> Refresh
			</button>
			<button class="btn btn-primary" onclick="location.href='orders.php?o=add'">
				<span>➕</span> New Order
			</button>
		</div>
	</div>

	<!-- Stats Grid -->
	<div class="stats-grid">
		<div class="stat-card animate" style="animation-delay: 0.1s">
			<div class="stat-header">
				<div class="stat-info">
					<h3>Total Products</h3>
					<div class="stat-value" id="totalProducts">0</div>
				</div>
				<div class="stat-icon primary">📦</div>
			</div>
			<div class="stat-footer">
				<span class="stat-change">In inventory</span>
				<a href="product.php" class="stat-link">View all →</a>
			</div>
		</div>

		<div class="stat-card success animate" style="animation-delay: 0.2s">
			<div class="stat-header">
				<div class="stat-info">
					<h3>Total Orders</h3>
					<div class="stat-value" id="activeOrders">0</div>
				</div>
				<div class="stat-icon success">🛒</div>
			</div>
			<div class="stat-footer">
				<span class="stat-change">Pending orders</span>
				<a href="orders.php?o=manord" class="stat-link">Manage →</a>
			</div>
		</div>

		<div class="stat-card danger animate" style="animation-delay: 0.3s">
			<div class="stat-header">
				<div class="stat-info">
					<h3>Low Stock Items</h3>
					<div class="stat-value" id="lowStock">0</div>
				</div>
				<div class="stat-icon danger">⚠️</div>
			</div>
			<div class="stat-footer">
				<span class="stat-change">Needs restock</span>
				<a href="product.php" class="stat-link">Review →</a>
			</div>
		</div>

		<div class="stat-card info animate" style="animation-delay: 0.4s">
			<div class="stat-header">
				<div class="stat-info">
					<h3>Today's Revenue</h3>
					<div class="stat-value" id="todayRevenue">PKR 0</div>
				</div>
				<div class="stat-icon info">💰</div>
			</div>
			<div class="stat-footer">
				<span class="stat-change" id="todayDate">-</span>
				<span class="stat-link">Today</span>
			</div>
		</div>

		<div class="stat-card warning animate" style="animation-delay: 0.5s">
			<div class="stat-header">
				<div class="stat-info">
					<h3>Month Revenue</h3>
					<div class="stat-value" id="monthRevenue">PKR 0</div>
				</div>
				<div class="stat-icon warning">📈</div>
			</div>
			<div class="stat-footer">
				<span class="stat-change" id="revenueGrowth">-</span>
				<span class="stat-link">This month</span>
			</div>
		</div>

		<div class="stat-card animate" style="animation-delay: 0.6s">
			<div class="stat-header">
				<div class="stat-info">
					<h3>Total Customers</h3>
					<div class="stat-value" id="totalCustomers">0</div>
				</div>
				<div class="stat-icon primary">👥</div>
			</div>
			<div class="stat-footer">
				<span class="stat-change">Unique clients</span>
				<span class="stat-link">All time</span>
			</div>
		</div>
	</div>

	<!-- Charts Section (only Revenue Trend now) -->
	<div class="charts-section">
		<div class="chart-card animate" style="animation-delay: 0.7s">
			<div class="chart-header">
				<h3>📈 Revenue Trend</h3>
				<div class="chart-filters">
					<button class="filter-btn active" onclick="changeRevenuePeriod(7, this)">7D</button>
					<button class="filter-btn" onclick="changeRevenuePeriod(14, this)">14D</button>
					<button class="filter-btn" onclick="changeRevenuePeriod(30, this)">30D</button>
				</div>
			</div>
			<div class="chart-container">
				<canvas id="revenueChart"></canvas>
			</div>
		</div>
	</div>
</div>

<script>
	// Initialize chart
	let revenueChart = null;
	let currentRevenuePeriod = 7;

	// Update date and time
	function updateDateTime() {
		const now = new Date();
		const options = {
			weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
			hour: '2-digit', minute: '2-digit'
		};
		document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
		document.getElementById('todayDate').textContent = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
	}

	// Format currency
	function formatCurrency(amount) {
		return 'PKR ' + Number(amount || 0).toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	// Format number
	function formatNumber(num) {
		return Number(num || 0).toLocaleString('en-US');
	}

	// Load dashboard statistics
	async function loadStats() {
		try {
			const response = await fetch(`dashboard.php?request=stats`);
			if (!response.ok) throw new Error(`HTTP ${response.status}`);

			const data = await response.json();

			if (data.success) {
				const stats = data.data;

				document.getElementById('totalProducts').textContent = formatNumber(stats.totalProducts);
				document.getElementById('activeOrders').textContent = formatNumber(stats.activeOrders);
				document.getElementById('lowStock').textContent = formatNumber(stats.lowStock);
				document.getElementById('todayRevenue').textContent = formatCurrency(stats.todayRevenue);
				document.getElementById('monthRevenue').textContent = formatCurrency(stats.monthRevenue);
				document.getElementById('totalCustomers').textContent = formatNumber(stats.totalCustomers);

				const growthEl = document.getElementById('revenueGrowth');
				const growth = Number(stats.revenueGrowth || 0);
				const arrow = growth >= 0 ? '↑' : '↓';
				growthEl.className = `stat-change ${growth >= 0 ? 'up' : 'down'}`;
				growthEl.textContent = `${arrow} ${Math.abs(growth).toFixed(1)}% vs last month`;
			}
		} catch (err) {
			console.error('Stats failed:', err);
		}
	}

	// Load revenue chart
	async function loadRevenueChart(days = 7) {
		try {
			const response = await fetch(`dashboard.php?request=revenue_chart&days=${days}`);
			if (!response.ok) throw new Error(`HTTP ${response.status}`);

			const data = await response.json();

			if (data.success) {
				const ctx = document.getElementById('revenueChart').getContext('2d');

				if (revenueChart) {
					revenueChart.destroy();
					revenueChart = null;
				}

				revenueChart = new Chart(ctx, {
					type: 'line',
					data: {
						labels: data.data.labels,
						datasets: [{
							label: 'Revenue (PKR)',
							data: data.data.values,
							borderColor: '#6366f1',
							backgroundColor: 'rgba(99, 102, 241, 0.12)',
							borderWidth: 3,
							fill: true,
							tension: 0.4,
							pointRadius: 5,
							pointBackgroundColor: '#6366f1',
							pointBorderColor: '#fff',
							pointBorderWidth: 2
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: { display: false },
							tooltip: {
								backgroundColor: 'rgba(0,0,0,0.85)',
								titleFont: { size: 14 },
								bodyFont: { size: 13 },
								padding: 12,
								cornerRadius: 8,
								callbacks: {
									label: ctx => formatCurrency(ctx.parsed.y)
								}
							}
						},
						scales: {
							y: {
								beginAtZero: true,
								ticks: { callback: v => 'PKR ' + v.toLocaleString() }
							},
							x: { grid: { display: false } }
						}
					}
				});
			}
		} catch (err) {
			console.error('Revenue chart failed:', err);
		}
	}

	// Change period
	function changeRevenuePeriod(days, button) {
		document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
		button.classList.add('active');
		currentRevenuePeriod = days;
		loadRevenueChart(days);
	}

	// Refresh dashboard
	function refreshDashboard() {
		loadStats();
		loadRevenueChart(currentRevenuePeriod);
		updateDateTime();
	}

	// Initialize
	document.addEventListener('DOMContentLoaded', () => {
		updateDateTime();
		refreshDashboard();

		// Auto refresh every 5 minutes
		setInterval(refreshDashboard, 300000);
		setInterval(updateDateTime, 60000);
	});
</script>

<?php require_once 'includes/footer.php'; ?>