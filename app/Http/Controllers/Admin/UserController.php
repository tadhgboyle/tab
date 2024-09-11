<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\UserRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Users\UserEditService;
use App\Services\Users\UserCreateService;
use App\Services\Users\UserDeleteService;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('pages.admin.users.list');
    }

    public function show(User $user)
    {
        return view('pages.admin.users.view', [
            'user' => $user,
        ]);
    }

    public function downloadPdf(User $user)
    {
        $timestamp = now()->timestamp;

        return Pdf::loadView('pdfs.admin.user', [
            'user' => $user,
        ])->stream("user-{$user->id}-{$timestamp}.pdf");
    }

    public function create(CategoryHelper $categoryHelper, RotationHelper $rotationHelper)
    {
        $categories = $categoryHelper->getCategories()->map(function (Category $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'limit' => [],
            ];
        });

        return view('pages.admin.users.form', [
            'available_roles' => auth()->user()->role->getRolesAvailable()->all(),
            'categories' => $categories,
            'rotations' => $rotationHelper->getRotations(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        return (new UserCreateService($request))->redirect();
    }

    public function edit(CategoryHelper $categoryHelper, RotationHelper $rotationHelper, User $user)
    {
        if (!auth()->user()->role->canInteract($user->role)) {
            return redirect()->route('users_list')->with('error', 'You cannot interact with that user.')->send();
        }

        $categories = $categoryHelper->getCategories()->map(function (Category $category) use ($user) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'limit' => $user->limitFor($category),
            ];
        });

        return view('pages.admin.users.form', [
            'user' => $user,
            'available_roles' => auth()->user()->role->getRolesAvailable()->all(),
            'categories' => $categories,
            'rotations' => $rotationHelper->getRotations(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        return (new UserEditService($request, $user))->redirect();
    }

    public function delete(User $user): RedirectResponse
    {
        return (new UserDeleteService($user))->redirect();
    }

    public function ajaxCheckLimit(User $user, Category $category)
    {
        $requestData = collect(json_decode(request()->query('products')));

        $products = Product::query()->whereIn('id', $requestData->keys())->get();
        $tryingToSpend = Money::parse(0);

        foreach ($products as $product) {
            foreach ($requestData->get($product->id) as $variantId => $quantity) {
                if ($variantId == 0) {
                    $tryingToSpend = $tryingToSpend->add($product->getPriceAfterTax()->multiply($quantity));
                    continue;
                }
                $variant = $product->variants()->find($variantId);
                $tryingToSpend = $tryingToSpend->add($variant->price->multiply($quantity)); // ->getPriceAfterTax()?
            }
        }

        $userLimit = $user->limitFor($category);

        return response()->json([
            'can_spend' => $userLimit->canSpend($tryingToSpend),
            'limit' => $userLimit->limit->format(),
            'duration' => $userLimit->duration(),
        ]);
    }
}
