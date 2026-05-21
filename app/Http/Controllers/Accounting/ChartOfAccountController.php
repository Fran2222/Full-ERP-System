<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.accounts.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $allowedSorts = ['code', 'name', 'type', 'normal_balance', 'is_active', 'created_at'];

        $sort = (string) $request->input('sort', 'code');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'code';
        }

        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $accounts = AccountingAccount::query()
            ->search($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('accounting.accounts.index', compact('accounts', 'perPage', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        $this->authorizeAccountingAccess('accounting.accounts.create');

        $account = new AccountingAccount([
            'is_active' => true,
        ]);

        return view('accounting.accounts.create', [
            'account' => $account,
            'types' => AccountingAccount::TYPES,
            'normalBalances' => AccountingAccount::NORMAL_BALANCES,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.accounts.create');

        $validated = $this->validatedData($request);

        AccountingAccount::create($validated);

        return redirect()
            ->route('accounting.accounts.index')
            ->with('success', 'Chart of account created successfully.');
    }

    public function show(AccountingAccount $account)
    {
        $this->authorizeAccountingAccess('accounting.accounts.view');

        return view('accounting.accounts.show', compact('account'));
    }

    public function edit(AccountingAccount $account)
    {
        $this->authorizeAccountingAccess('accounting.accounts.edit');

        return view('accounting.accounts.edit', [
            'account' => $account,
            'types' => AccountingAccount::TYPES,
            'normalBalances' => AccountingAccount::NORMAL_BALANCES,
        ]);
    }

    public function update(Request $request, AccountingAccount $account)
    {
        $this->authorizeAccountingAccess('accounting.accounts.edit');

        $validated = $this->validatedData($request, $account);

        $account->update($validated);

        return redirect()
            ->route('accounting.accounts.index')
            ->with('success', 'Chart of account updated successfully.');
    }

    public function destroy(AccountingAccount $account)
    {
        $this->authorizeAccountingAccess('accounting.accounts.delete');

        $account->delete();

        return redirect()
            ->route('accounting.accounts.index')
            ->with('success', 'Chart of account deleted successfully.');
    }

    private function validatedData(Request $request, ?AccountingAccount $account = null): array
    {
        $accountId = $account?->id;

        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounting_accounts', 'code')->ignore($accountId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(AccountingAccount::TYPES))],
            'normal_balance' => ['required', Rule::in(array_keys(AccountingAccount::NORMAL_BALANCES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function authorizeAccountingAccess(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])
            ),
            403
        );
    }
}
