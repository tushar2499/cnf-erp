<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\Report\BalanceSheetReportRequest;
use App\Http\Requests\Admin\Accounting\Report\CashBookReportRequest;
use App\Http\Requests\Admin\Accounting\Report\DayBookReportRequest;
use App\Http\Requests\Admin\Accounting\Report\LedgerReportRequest;
use App\Http\Requests\Admin\Accounting\Report\ProfitLossReportRequest;
use App\Http\Requests\Admin\Accounting\Report\TrialBalanceReportRequest;
use App\Models\Accounting\AccAccount;
use App\Models\Company;
use App\Services\Accounting\PartyDirectory;
use App\Services\Accounting\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function ledger(LedgerReportRequest $request)
    {
        [$from, $to] = $this->period($request);
        $account = $request->account_id ? AccAccount::find($request->account_id) : null;
        $ledger = null;

        if ($account && ! $account->is_group) {
            $ledger = $this->reports->ledger($account, $this->companyId($request), $from, $to,
                $request->party_type ?: null, $request->integer('party_id') ?: null);
        }

        return view('admin.accounting.reports.ledger', $this->common($request) + [
            'from'       => $from, 'to' => $to, 'account' => $account, 'ledger' => $ledger,
            'partyTypes' => PartyDirectory::TYPES,
        ]);
    }

    public function trialBalance(TrialBalanceReportRequest $request)
    {
        [$from, $to] = $this->period($request);
        $level = $request->integer('level') ?: AccAccount::LEVEL_LEDGER;

        return view('admin.accounting.reports.trial-balance', $this->common($request) + [
            'from'   => $from, 'to' => $to, 'level' => $level,
            'report' => $this->reports->trialBalance($this->companyId($request), $from, $to, $level),
        ]);
    }

    public function profitLoss(ProfitLossReportRequest $request)
    {
        [$from, $to] = $this->period($request);

        return view('admin.accounting.reports.profit-loss', $this->common($request) + [
            'from'   => $from, 'to' => $to,
            'report' => $this->reports->profitLoss($this->companyId($request), $from, $to),
        ]);
    }

    public function balanceSheet(BalanceSheetReportRequest $request)
    {
        $asOn = $request->as_on ?: now()->toDateString();

        return view('admin.accounting.reports.balance-sheet', $this->common($request) + [
            'asOn'   => $asOn,
            'report' => $this->reports->balanceSheet($this->companyId($request), $asOn),
        ]);
    }

    public function dayBook(DayBookReportRequest $request)
    {
        [$from, $to] = $this->period($request, false);

        return view('admin.accounting.reports.day-book', $this->common($request) + [
            'from'     => $from, 'to' => $to, 'type' => $request->type,
            'vouchers' => $this->reports->dayBook($this->companyId($request), $from, $to, $request->type ?: null),
        ]);
    }

    public function cashBook(CashBookReportRequest $request)
    {
        [$from, $to] = $this->period($request);
        $accountId = $request->integer('account_id') ?: null;

        return view('admin.accounting.reports.cash-book', $this->common($request) + [
            'from'         => $from, 'to' => $to, 'accountId' => $accountId,
            'cashAccounts' => AccAccount::where('is_cash_bank', true)->where('is_group', false)->orderBy('code')->get(['id', 'code', 'name']),
            'books'        => $this->reports->cashBook($this->companyId($request), $from, $to, $accountId),
        ]);
    }

    /**
     * Default period: current month (day book: today).
     *
     * @return array{0: string, 1: string}
     */
    private function period(Request $request, bool $monthDefault = true): array
    {
        $from = $request->from ?: ($monthDefault ? now()->startOfMonth()->toDateString() : now()->toDateString());
        $to = $request->to ?: now()->toDateString();

        return [$from, $to];
    }

    private function companyId(Request $request): ?int
    {
        return $request->integer('company_id') ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    private function common(Request $request): array
    {
        $companies = Company::orderBy('id')->get(['id', 'name']);
        $companyId = $this->companyId($request);

        return [
            'companies'   => $companies,
            'companyId'   => $companyId,
            'companyName' => $companyId ? $companies->firstWhere('id', $companyId)?->name : 'All Companies (Group)',
        ];
    }
}
