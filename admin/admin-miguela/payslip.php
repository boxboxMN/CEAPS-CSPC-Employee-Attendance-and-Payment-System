<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
include 'db_conn.php';

// Check if payslip data exists in session
if (!isset($_SESSION['payslip_data'])) {
    header("Location: payroll.php");
    exit();
}

$payslip_data = $_SESSION['payslip_data'];
$employee_id = $payslip_data['employee_id'];
$date_range = $payslip_data['date_range'];
$start_date = $payslip_data['start_date'];
$end_date = $payslip_data['end_date'];

// Get employee details
$employee_query = "SELECT e.*, r.role, r.rate_per_hour 
                   FROM employees e 
                   LEFT JOIN roles r ON e.role_id = r.role_id 
                   WHERE e.employee_id = ?";
$stmt = $conn->prepare($employee_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$employee_result = $stmt->get_result();

if ($employee_result->num_rows === 0) {
    die("Employee not found");
}

$employee = $employee_result->fetch_assoc();

// Calculate hours worked and gross pay for the selected period
$hours_query = "SELECT SUM(TIMESTAMPDIFF(HOUR, a.time_in, IFNULL(a.time_out, NOW()))) AS total_hours
                FROM attendance a
                WHERE a.employee_id = ? AND a.date BETWEEN ? AND ?";
$stmt = $conn->prepare($hours_query);
$stmt->bind_param("sss", $employee_id, $start_date, $end_date);
$stmt->execute();
$hours_result = $stmt->get_result();
$hours_data = $hours_result->fetch_assoc();

$total_hours = $hours_data['total_hours'] ?? 0;
$basic_salary = $total_hours * $employee['rate_per_hour'];

// Set other pay components (you can modify these as needed)
$housing_allowance = 5000.00;
$transportation_allowance = 3000.00;
$bonus = 0.00;
$deductions = 1500.00;

// Calculate totals
$gross = $basic_salary + $housing_allowance + $transportation_allowance + $bonus;
$net_pay = $gross - $deductions;

// Get current month and year for pay period
$pay_period = date('F j, Y', strtotime($start_date)) . ' - ' . date('F j, Y', strtotime($end_date));

// Format dates
$issue_date = date('d/m/Y');
$payment_date = date('d/m/Y', strtotime('+5 days'));
$next_pay_date = date('d/m/Y', strtotime('+1 month'));

// Generate payment reference
$payment_ref = 'PAY-' . strtoupper(uniqid());

// Calculate YTD values
$ytd_query = "SELECT 
                SUM(TIMESTAMPDIFF(HOUR, a.time_in, IFNULL(a.time_out, NOW()))) * r.rate_per_hour AS ytd_gross
              FROM 
                employees e
              JOIN 
                attendance a ON e.employee_id = a.employee_id
              JOIN 
                roles r ON e.role_id = r.role_id
              WHERE 
                e.employee_id = ? AND YEAR(a.date) = YEAR(CURDATE())";
$stmt = $conn->prepare($ytd_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$ytd_result = $stmt->get_result();
$ytd_data = $ytd_result->fetch_assoc();

$ytd_gross = $ytd_data['ytd_gross'] ?? 0;
$ytd_allowances = ($housing_allowance + $transportation_allowance) * date('n');
$ytd_deductions = $deductions * date('n');
$ytd_total = $ytd_gross + $ytd_allowances - $ytd_deductions;

// Clear the session data after use
unset($_SESSION['payslip_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Payslip | Camarines Sur Polytechnic</title>
    <style>
        @page {
            size: landscape;
            margin: 0;
        }
        
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 10px;
        }
        
        .payslip-container {
            width: 280mm;
            height: 190mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #2980b9);
            color: white;
            padding: 5px 15px;
            text-align: center;
            border-bottom: 3px solid #3498db;
        }
        
        .company-logo {
            height: 40px;
            margin-bottom: 5px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .payslip-title {
            font-size: 14px;
            font-weight: 400;
            opacity: 0.9;
        }
        
        .content-wrapper {
            display: flex;
            padding: 5px;
            height: calc(100% - 120px);
        }
        
        .left-column, .right-column {
            flex: 1;
            padding: 5px;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            background-color: #f5f7fa;
            padding: 5px 10px;
            font-size: 10px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .section {
            padding: 8px 10px;
            border-bottom: 1px solid #e0e6ed;
            margin-bottom: 8px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .section-title:before {
            content: "";
            display: inline-block;
            width: 3px;
            height: 12px;
            background-color: #3498db;
            margin-right: 8px;
        }
        
        .employee-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
        }
        
        .detail-item {
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-size: 9px;
            font-weight: 500;
            color: #7f8c8d;
            margin-bottom: 2px;
        }
        
        .detail-value {
            font-size: 10px;
            font-weight: 500;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 9px;
        }
        
        th {
            background-color: #f5f7fa;
            color: #2c3e50;
            text-align: left;
            padding: 5px 8px;
            font-weight: 600;
            font-size: 9px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            font-weight: 600;
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .net-pay {
            font-size: 11px;
            color: #27ae60;
            font-weight: 600;
        }
        
        .notes {
            font-size: 8px;
            color: #7f8c8d;
            margin-top: 5px;
            padding: 5px;
            background-color: #f5f7fa;
            border-left: 2px solid #3498db;
        }
        
        .footer {
            padding: 5px 10px;
            background-color: #2c3e50;
            color: white;
            font-size: 8px;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        
        .confidential {
            font-weight: 600;
            color: #ffdd59;
            font-size: 9px;
            text-align: center;
            margin: 3px 0;
        }
        
        .disclaimer {
            opacity: 0.8;
            font-size: 8px;
            text-align: center;
            margin: 3px 0;
        }
        
        .stamp {
            float: right;
            margin-top: 10px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 8px;
        }
        
        .stamp-placeholder {
            width: 80px;
            height: 40px;
            border: 1px dashed #e0e6ed;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 3px;
            color: #7f8c8d;
            font-size: 8px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            margin-bottom: 5px;
        }
        
        .generation-info {
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <img src="https://wikiwandv2-19431.kxcdn.com/_next/image?url=https://upload.wikimedia.org/wikipedia/commons/thumb/9/95/Camarines_Sur_Polytechnic_Colleges_Logo.png/640px-Camarines_Sur_Polytechnic_Colleges_Logo.png&w=640&q=50" alt="Company Logo" class="company-logo">
            <div class="company-name">Camarines Sur Polytechnic</div>
            <div class="payslip-title">PAYSLIP FOR PERIOD: <?= htmlspecialchars($pay_period) ?></div>
        </div>
        
        <div class="meta-info">
            <div>
                <span><strong>Payslip Period:</strong> <?= htmlspecialchars($pay_period) ?></span>
                <span><strong>Issue Date:</strong> <?= $issue_date ?></span>
            </div>
            <div>
                <span><strong>Employee ID:</strong> <?= htmlspecialchars($employee_id) ?></span>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="left-column">
                <div class="section">
                    <div class="section-title">Employee Details</div>
                    <div class="employee-details">
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value"><?= htmlspecialchars($employee['department']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Designation</div>
                            <div class="detail-value"><?= htmlspecialchars($employee['role']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Hourly Rate</div>
                            <div class="detail-value">₱<?= number_format($employee['rate_per_hour'], 2) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">Earnings</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount (₱)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary (<?= $total_hours ?> hours)</td>
                                <td class="text-right"><?= number_format($basic_salary, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Housing Allowance</td>
                                <td class="text-right"><?= number_format($housing_allowance, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Transportation Allowance</td>
                                <td class="text-right"><?= number_format($transportation_allowance, 2) ?></td>
                            </tr>
                            <?php if($bonus > 0): ?>
                            <tr>
                                <td>Bonus</td>
                                <td class="text-right"><?= number_format($bonus, 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <td>Total Gross Earnings</td>
                                <td class="text-right"><?= number_format($gross, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="section">
                    <div class="section-title">Net Pay Summary</div>
                    <table>
                        <tr>
                            <td>Total Gross Earnings</td>
                            <td class="text-right"><?= number_format($gross, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Total Deductions</td>
                            <td class="text-right"><?= number_format($deductions, 2) ?></td>
                        </tr>
                        <tr class="total-row">
                            <td class="net-pay">Net Pay</td>
                            <td class="text-right net-pay"><?= number_format($net_pay, 2) ?></td>
                        </tr>
                    </table>
                    
                    <div class="stamp">
                        <div class="stamp-placeholder">AUTHORIZED SIGNATURE</div>
                        <div>For Camarines Sur Polytechnic</div>
                    </div>
                </div>
            </div>
            
            <div class="right-column">
                <div class="section">
                    <div class="section-title">Payment Details</div>
                    <div class="employee-details">
                        <div class="detail-item">
                            <div class="detail-label">Payment Date</div>
                            <div class="detail-value"><?= $payment_date ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Payment Reference</div>
                            <div class="detail-value"><?= $payment_ref ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Bank Account</div>
                            <div class="detail-value">•••• 1234</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">YTD Summary</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount (₱)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary YTD</td>
                                <td class="text-right"><?= number_format($ytd_gross, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Allowances YTD</td>
                                <td class="text-right"><?= number_format($ytd_allowances, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Deductions YTD</td>
                                <td class="text-right"><?= number_format($ytd_deductions, 2) ?></td>
                            </tr>
                            <tr class="total-row">
                                <td>Net Earnings YTD</td>
                                <td class="text-right"><?= number_format($ytd_total, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="section">
                    <div class="section-title">Additional Information</div>
                    <div class="notes">
                        Next pay date: <?= $next_pay_date ?><br>
                        For any discrepancies, please contact HR within 7 days of receipt.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="confidential">
                CONFIDENTIAL DOCUMENT - UNAUTHORIZED ACCESS PROHIBITED
            </div>
            <div class="disclaimer">
                This is a computer-generated document and does not require a physical signature.
            </div>
            <div class="generation-info">
                Document generated on <?= date('d/m/Y H:i') ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 1000);
        });
    </script>
</body>
</html>