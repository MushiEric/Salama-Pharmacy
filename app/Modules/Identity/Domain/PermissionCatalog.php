<?php

namespace App\Modules\Identity\Domain;

final class PermissionCatalog
{
    public const BRANCH_VIEW = 'branch.view';

    public const BRANCH_MANAGE = 'branch.manage';

    public const USER_VIEW = 'user.view';

    public const USER_CREATE = 'user.create';

    public const USER_UPDATE = 'user.update';

    public const ROLE_MANAGE = 'role.manage';

    public const DRUG_VIEW = 'drug.view';

    public const PRODUCT_CREATE = 'product.create';

    public const PRODUCT_UPDATE = 'product.update';

    public const PRODUCT_PRICE_UPDATE = 'product.price.update';

    public const SUPPLIER_VIEW = 'supplier.view';

    public const SUPPLIER_MANAGE = 'supplier.manage';

    public const INVENTORY_VIEW = 'inventory.view';

    public const INVENTORY_RECEIVE = 'inventory.receive';

    public const INVENTORY_ADJUST = 'inventory.adjust';

    public const TRANSFER_CREATE = 'transfer.create';

    public const TRANSFER_APPROVE = 'transfer.approve';

    public const TRANSFER_DISPATCH = 'transfer.dispatch';

    public const TRANSFER_RECEIVE = 'transfer.receive';

    public const PRESCRIPTION_CREATE = 'prescription.create';

    public const PRESCRIPTION_DISPENSE = 'prescription.dispense';

    public const SALE_CREATE = 'sale.create';

    public const SALE_DISCOUNT_APPLY = 'sale.discount.apply';

    public const SALE_DISCOUNT_OVERRIDE = 'sale.discount.override';

    public const SALE_VIEW = 'sale.view';

    public const REPORT_SALES_VIEW = 'report.sales.view';

    public const REPORT_INVENTORY_VIEW = 'report.inventory.view';

    public const REPORT_PROFIT_VIEW = 'report.profit.view';

    public const SETTINGS_MANAGE = 'settings.manage';

    public const SUBSCRIPTION_VIEW = 'subscription.view';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            self::BRANCH_VIEW => 'View branches',
            self::BRANCH_MANAGE => 'Manage branches',
            self::USER_VIEW => 'View users',
            self::USER_CREATE => 'Create users',
            self::USER_UPDATE => 'Update users',
            self::ROLE_MANAGE => 'Manage roles and permission assignments',
            self::DRUG_VIEW => 'View master drug catalog',
            self::PRODUCT_CREATE => 'Create pharmacy products',
            self::PRODUCT_UPDATE => 'Update pharmacy products',
            self::PRODUCT_PRICE_UPDATE => 'Update branch prices',
            self::SUPPLIER_VIEW => 'View suppliers',
            self::SUPPLIER_MANAGE => 'Manage suppliers',
            self::INVENTORY_VIEW => 'View inventory',
            self::INVENTORY_RECEIVE => 'Receive stock',
            self::INVENTORY_ADJUST => 'Adjust stock',
            self::TRANSFER_CREATE => 'Create transfers',
            self::TRANSFER_APPROVE => 'Approve transfers',
            self::TRANSFER_DISPATCH => 'Dispatch transfers',
            self::TRANSFER_RECEIVE => 'Receive transfers',
            self::PRESCRIPTION_CREATE => 'Create prescriptions',
            self::PRESCRIPTION_DISPENSE => 'Dispense prescriptions',
            self::SALE_CREATE => 'Create sales',
            self::SALE_DISCOUNT_APPLY => 'Apply sale discounts',
            self::SALE_DISCOUNT_OVERRIDE => 'Override sale discounts',
            self::SALE_VIEW => 'View sales',
            self::REPORT_SALES_VIEW => 'View sales reports',
            self::REPORT_INVENTORY_VIEW => 'View inventory reports',
            self::REPORT_PROFIT_VIEW => 'View profit reports',
            self::SETTINGS_MANAGE => 'Manage pharmacy settings',
            self::SUBSCRIPTION_VIEW => 'View subscription information',
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }
}
