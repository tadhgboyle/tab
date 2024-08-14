<?php

namespace App\Http\Controllers;

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
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class UserController extends Controller
{
    public function index(RotationHelper $rotationHelper)
    {
        $user_list_rotation_id = $rotationHelper->getUserListRotationId();

        if ($user_list_rotation_id === null) {
            $data = [
                'cannot_view_users' => true,
            ];
        } else {
            $users = User::query()->when($user_list_rotation_id !== '*', function (EloquentBuilder $query) use ($user_list_rotation_id) {
                $query->whereHas('rotations', function (EloquentBuilder $query) use ($user_list_rotation_id) {
                    return $query->where('rotation_id', $user_list_rotation_id);
                });
            })->with('role', 'rotations')->get();

            $data = [
                'rotations' => $rotationHelper->getRotations(),
                'users' => $users,
                'user_list_rotation_id' => $user_list_rotation_id,
            ];
        }

        return view('pages.users.list', $data);
    }

    public function show(CategoryHelper $categoryHelper, User $user)
    {
        $categories = $categoryHelper->getCategories()->mapWithKeys(function ($category) use ($user) {
            $userLimit = $user->limitFor($category);

            return [
                $category->id => [
                    'name' => $category->name,
                    'limit' => $userLimit->limit,
                    'duration' => $userLimit->duration(),
                    'spent' => $userLimit->findSpent(),
                ],
            ];
        });

        return view('pages.users.view', [
            'user' => $user,
            'is_self' => $user->id === auth()->id(),
            'can_interact' => auth()->user()->role->canInteract($user->role),
            'activity_registrations' => $user->activityRegistrations,
            'categories' => $categories,
            'rotations' => $user->rotations,
        ]);
    }

    public function downloadPdf(User $user)
    {
        $timestamp = now()->timestamp;

        return Pdf::loadView('pdfs.users.orders', [
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

        return view('pages.users.form', [
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
        if ($user->trashed()) {
            return redirect()->route('users_list')->with('error', 'That user has been deleted.')->send();
        }

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

        return view('pages.users.form', [
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

        $tryingToSpend = Money::sum(...$products->map(function (Product $product) use ($requestData) {
            return $product->getPriceAfterTax()->multiply($requestData->get($product->id));
        }));

        $userLimit = $user->limitFor($category);

        return response()->json([
            'can_spend' => $userLimit->canSpend($tryingToSpend),
            'limit' => $userLimit->limit->format(),
            'duration' => $userLimit->duration(),
        ]);
    }
}
