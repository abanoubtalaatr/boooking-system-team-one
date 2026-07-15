<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case CardCredit = 'card_credit';
    case CashCommissionDebit = 'cash_commission_debit';
    case RefundDebit = 'refund_debit';
    case WithdrawalDebit = 'withdrawal_debit';
    case Adjustment = 'adjustment';
}
