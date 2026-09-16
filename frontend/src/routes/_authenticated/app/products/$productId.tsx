import { createFileRoute, redirect } from '@tanstack/react-router';
import { userQueryOptions } from '@/features/auth/api';
import { ProductDetailPage } from '@/features/catalog/product-detail-page';
import { can, Permission } from '@/shared/lib/permissions';

export const Route = createFileRoute('/_authenticated/app/products/$productId')({
    beforeLoad: async ({ context }) => {
        const user = await context.queryClient.ensureQueryData(userQueryOptions());

        if (!can(user, Permission.DRUG_VIEW)) {
            throw redirect({ to: '/app' });
        }
    },
    component: ProductDetailRoute,
});

function ProductDetailRoute() {
    const { productId } = Route.useParams();

    return <ProductDetailPage productId={productId} />;
}
