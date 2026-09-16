import { createFileRoute, redirect } from '@tanstack/react-router';
import { userQueryOptions } from '@/features/auth/api';
import { ProductsPage } from '@/features/catalog/products-page';
import { can, Permission } from '@/shared/lib/permissions';

export const Route = createFileRoute('/_authenticated/app/products/')({
  beforeLoad: async ({ context }) => {
    const user = await context.queryClient.ensureQueryData(userQueryOptions());

    if (!can(user, Permission.DRUG_VIEW)) {
      throw redirect({ to: '/app' });
    }
  },
  component: ProductsPage,
});
