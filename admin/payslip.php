<?php
session_start();
if (!isset($_SESSION['payslip_data'])) {
    header("Location: payroll.php");
    exit();
}

$payslip = $_SESSION['payslip_data'];
$period = $_SESSION['payslip_period'];
$dates = explode(' - ', $period);

// Calculate YTD values (you'll need to implement proper YTD calculations)
$ytd_gross = $payslip['gross'] * 12; // Simplified example
$ytd_allowances = ($payslip['housing_allowance'] + $payslip['transportation_allowance']) * 12;
$ytd_bonus = $payslip['bonus'] ?? 0;
$ytd_total = $ytd_gross + $ytd_allowances + $ytd_bonus;

// Format dates
$pay_period = date('F Y', strtotime($dates[0]));
$issue_date = date('d/m/Y');
$payment_date = date('d/m/Y', strtotime($dates[1]));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Payslip | <?= htmlspecialchars($payslip['company_name'] ?? 'Camarines Sur Polytechnic') ?></title>
    <style>
        /* Your existing CSS styles here */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #2980b9;
            --text-color: #333;
            --light-gray: #f5f7fa;
            --medium-gray: #e0e6ed;
            --dark-gray: #7f8c8d;
            --success-color: #27ae60;
        }
        
        @page {
            size: landscape;
        }
        
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            color: var(--text-color);
            background-color: #f9f9f9;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding: 20px;
        }
        
        .payslip-container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 20px 30px;
            text-align: center;
            border-bottom: 4px solid var(--secondary-color);
        }
        
        .company-logo {
            height: 50px;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .payslip-title {
            font-size: 16px;
            font-weight: 400;
            opacity: 0.9;
        }
        
        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
        }
        
        .left-column {
            flex: 1;
            min-width: 400px;
            padding: 15px;
        }
        
        .right-column {
            flex: 1;
            min-width: 400px;
            padding: 15px;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            background-color: var(--light-gray);
            padding: 12px 20px;
            font-size: 12px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .meta-info span {
            display: inline-block;
            margin-right: 15px;
        }
        
        .meta-info strong {
            color: var(--primary-color);
        }
        
        .section {
            padding: 15px 20px;
            border-bottom: 1px solid var(--medium-gray);
            margin-bottom: 15px;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .section-title:before {
            content: "";
            display: inline-block;
            width: 4px;
            height: 16px;
            background-color: var(--secondary-color);
            margin-right: 10px;
            border-radius: 2px;
        }
        
        .employee-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-size: 14px;
            font-weight: 500;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 13px;
        }
        
        th {
            background-color: var(--light-gray);
            color: var(--primary-color);
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--medium-gray);
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            font-weight: 600;
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .total-row td {
            border-top: 1px solid var(--medium-gray);
            border-bottom: none;
        }
        
        .net-pay {
            font-size: 15px;
            color: var(--success-color);
            font-weight: 600;
        }
        
        .notes {
            font-size: 12px;
            color: var(--dark-gray);
            margin-top: 10px;
            padding: 8px 12px;
            background-color: var(--light-gray);
            border-radius: 4px;
            border-left: 3px solid var(--secondary-color);
        }
        
        .footer {
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            font-size: 11px;
        }
        
        .footer p {
            margin: 5px 0;
            line-height: 1.6;
        }
        
        .confidential {
            font-weight: 600;
            color: #ffdd59;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .disclaimer {
            opacity: 0.8;
            font-size: 10px;
            text-align: center;
        }
        
        .stamp {
            float: right;
            margin-top: 15px;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        
        .stamp-placeholder {
            width: 100px;
            height: 50px;
            border: 2px dashed var(--medium-gray);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            color: var(--dark-gray);
            font-size: 10px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .footer-col {
            text-align: left;
        }
        
        .footer-col.right {
            text-align: right;
        }
        
        .generation-info {
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 10px;
            margin-top: 10px;
            text-align: center;
        }
        
        .lock-icon {
            width: 12px;
            height: 12px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <img src="https://wikiwandv2-19431.kxcdn.com/_next/image?url=https://upload.wikimedia.org/wikipedia/commons/thumb/9/95/Camarines_Sur_Polytechnic_Colleges_Logo.png/640px-Camarines_Sur_Polytechnic_Colleges_Logo.png&w=640&q=50" alt="Company Logo" class="company-logo">
            <div class="company-name">Camarines Sur Polytechnic</div>
            <div class="payslip-title">MONTHLY PAYSLIP</div>
        </div>
        
        <div class="meta-info">
            <div>
                <span><strong>Payslip Period:</strong> <?= htmlspecialchars($pay_period) ?></span>
                <span><strong>Issue Date:</strong> <?= $issue_date ?></span>
            </div>
            <div>
                <span><strong>Employee ID:</strong> <?= htmlspecialchars($payslip['employee_id']) ?></span>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="left-column">
                <div class="section">
                    <div class="section-title">Employee Details</div>
                    <div class="employee-details">
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?= htmlspecialchars($payslip['name']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value"><?= htmlspecialchars($payslip['department'] ?? 'N/A') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Designation</div>
                            <div class="detail-value"><?= htmlspecialchars($payslip['position'] ?? 'N/A') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Payment Method</div>
                            <div class="detail-value"><?= htmlspecialchars($payslip['payment_method'] ?? 'Bank Transfer') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Bank Account No.</div>
                            <div class="detail-value">•••• <?= substr(htmlspecialchars($payslip['account_number'] ?? '0000'), -4) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">Earnings</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary</td>
                                <td class="text-right"><?= number_format($payslip['basic_salary'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Housing Allowance</td>
                                <td class="text-right"><?= number_format($payslip['housing_allowance'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Transportation Allowance</td>
                                <td class="text-right"><?= number_format($payslip['transportation_allowance'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Overtime Pay</td>
                                <td class="text-right"><?= number_format($payslip['overtime_pay'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Performance Bonus</td>
                                <td class="text-right"><?= number_format($payslip['bonus'] ?? 0, 2) ?></td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Gross Earnings</td>
                                <td class="text-right"><?= number_format($payslip['gross'], 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="notes">
                        Note: <?= ($payslip['deductions'] > 0) ? 
                            'Deductions applied: $'.number_format($payslip['deductions'], 2) : 
                            'No deductions applied for this pay period.' ?>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">Net Pay Summary</div>
                    <table>
                        <tr>
                            <td>Total Gross Earnings</td>
                            <td class="text-right"><?= number_format($payslip['gross'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Total Deductions</td>
                            <td class="text-right"><?= number_format($payslip['deductions'] ?? 0, 2) ?></td>
                        </tr>
                        <tr class="total-row">
                            <td class="net-pay">Net Pay (Take-Home Salary)</td>
                            <td class="text-right net-pay"><?= number_format($payslip['net_pay'], 2) ?></td>
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
                            <div class="detail-value">PAY-<?= strtoupper(uniqid()) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Bank Name</div>
                            <div class="detail-value"><?= htmlspecialchars($payslip['bank_name'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">YTD Summary</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount (USD)</th>
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
                                <td>Bonuses YTD</td>
                                <td class="text-right"><?= number_format($ytd_bonus, 2) ?></td>
                            </tr>
                            <tr class="total-row">
                                <td>Gross Earnings YTD</td>
                                <td class="text-right"><?= number_format($ytd_total, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Tax Paid YTD</td>
                                <td class="text-right"><?= number_format($payslip['tax_ytd'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Net Pay YTD</td>
                                <td class="text-right"><?= number_format($ytd_total - ($payslip['tax_ytd'] ?? 0), 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="section">
                    <div class="section-title">Additional Information</div>
                    <div class="employee-details">
                        <div class="detail-item">
                            <div class="detail-label">Leave Balance</div>
                            <div class="detail-value"><?= $payslip['leave_balance'] ?? '12' ?> days remaining</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tax Code</div>
                            <div class="detail-value"><?= htmlspecialchars($payslip['tax_code'] ?? 'N/A') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Pay Frequency</div>
                            <div class="detail-value">Monthly</div>
                        </div>
                    </div>
                    <div class="notes">
                        Next pay date: <?= date('d/m/Y', strtotime('+1 month', strtotime($dates[1]))) ?><br>
                        For any discrepancies, please contact HR within 7 days of receipt.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-grid">
                <div class="footer-col">
                    <p><strong>HR Department</strong></p>
                    <p>Email: hr@company.com</p>
                    <p>Phone: +1 (555) 123-4567</p>
                </div>
                <div class="footer-col right">
                    <p><strong>Finance Department</strong></p>
                    <p>Email: finance@company.com</p>
                    <p>Phone: +1 (555) 987-6543</p>
                </div>
            </div>
            
            <div class="generation-info">
                <p class="confidential">
                    <svg class="lock-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    CONFIDENTIAL DOCUMENT - UNAUTHORIZED ACCESS PROHIBITED
                </p>
                <p class="disclaimer">
                    This is a computer-generated document and does not require a physical signature.<br>
                    Please retain this payslip for your records and tax purposes.
                </p>
                <p style="font-size: 10px; opacity: 0.6; margin-top: 10px;">
                    Document generated on <?= date('d/m/Y H:i') ?> | Payslip Version 2.1
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-populate current date and time
        document.addEventListener('DOMContentLoaded', function() {
            // Print the payslip automatically
            window.print();
            
            // Close the window after printing (optional)
            setTimeout(function() {
                window.close();
            }, 1000);
        });
    </script>
</body>
</html>