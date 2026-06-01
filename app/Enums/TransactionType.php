<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income  = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match($this) {
            self::Income  => 'Income',
            self::Expense => 'Expense',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Income  => 'bg-success',
            self::Expense => 'bg-danger',
        };
    }

    public static function categories(self $type): array
    {
        return match($type) {
            self::Income  => [
                'Project Revenue',
                'Hourly Billing',
                'Retainer',
                'Outsourcing Margin',
                'Consultation Fee',
                'Maintenance Contract',
                'Reimbursement',
                'Other Income',
            ],
            self::Expense => [
                'Freelancer Payments',
                'Software & Subscriptions',
                'Domain & Hosting',
                'Salaries & Payroll',
                'Marketing & Ads',
                'Professional Development',
                'Office & Utilities',
                'Legal & Accounting',
                'Equipment & Hardware',
                'Bank & Payment Fees',
                'Travel & Transport',
                'Client Entertainment',
                'Other Expense',
            ],
        };
    }
}
