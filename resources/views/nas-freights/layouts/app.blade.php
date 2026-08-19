@extends('layouts.app')

@section('sidebar')
    <div class="pt-2 pb-4">
        <div class="px-3 py-2 mb-1"
            style="font-size:.7rem; color:#6b7a99; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
            NAS Freights
        </div>

        @php
            $operationsActive = request()->routeIs(
                'nas-freights.bookings.*',
                'nas-freights.customer-bills.*',
                'nas-freights.supplier-bills.*',
            );
            $freightImportActive = request()->routeIs('nas-freights.rfqs.*', 'nas-freights.freight-bookings.*');
            $freightExportActive = false;

            $dueListsActive = request()->routeIs('nas-freights.due-lists.*');
            $collectionsActive = request()->routeIs(
                'nas-freights.money-receipts.*',
                'nas-freights.supplier-payments.*',
            );
            $reportsActive = request()->routeIs('nas-freights.reports.*');
            $stakeholdersActive = request()->routeIs('nas-freights.stakeholders.*');
            $importActive = request()->routeIs('nas-freights.import.*');
            $settingsActive = request()->routeIs('nas-freights.settings.*');
        @endphp

        <div class="nav-item-group">
            <div class="nav-section">Main</div>
            <a href="{{ route('nas-freights.dashboard') }}"
                class="nav-link {{ request()->routeIs('nas-freights.dashboard') ? 'active' : '' }}">
                <i class="fa fa-tachometer-alt"></i> Dashboard
            </a>
        </div>

        {{-- Operations --}}
        <div class="nav-item-group">
            <div class="nav-section">Local Logistics</div>
            <a href="#freightOperationsMenu" class="nav-link {{ $operationsActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $operationsActive ? 'true' : 'false' }}"
                aria-controls="freightOperationsMenu">
                <i class="fa fa-truck"></i><span> Local Logistics</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $operationsActive ? 'show' : '' }}" id="freightOperationsMenu">
                <a href="{{ route('nas-freights.bookings.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.bookings.*') ? 'active' : '' }}">
                    <i class="fa fa-truck"></i> Transport Bookings
                </a>
                <a href="{{ route('nas-freights.customer-bills.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.customer-bills.*') ? 'active' : '' }}">
                    <i class="fa fa-file-invoice-dollar"></i> Customer Bills
                </a>
                <a href="{{ route('nas-freights.supplier-bills.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.supplier-bills.*') ? 'active' : '' }}">
                    <i class="fa fa-file-invoice"></i> Supplier Bills
                </a>
            </div>
        </div>

        {{-- Freight Operations --}}
        <div class="nav-item-group">
            <div class="nav-section">Freight Import</div>
            <a href="#freightFreightOpsMenu" class="nav-link {{ $freightImportActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $freightImportActive ? 'true' : 'false' }}"
                aria-controls="freightFreightOpsMenu">
                <i class="fa fa-ship"></i><span> Freight Import</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $freightImportActive ? 'show' : '' }}" id="freightFreightOpsMenu">
                <a href="{{ route('nas-freights.rfqs.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.rfqs.*') ? 'active' : '' }}">
                    <i class="fa fa-file-signature"></i> RFQs
                </a>
                <a href="{{ route('nas-freights.freight-bookings.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.freight-bookings.*') ? 'active' : '' }}">
                    <i class="fa fa-ship"></i> Freight Bookings
                </a>
            </div>
        </div>

        {{-- Freight Operations --}}
        <div class="nav-item-group">
            <div class="nav-section">Freight Export</div>
            <a href="#freightFreightExportMenu" class="nav-link {{ $freightExportActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $freightExportActive ? 'true' : 'false' }}"
                aria-controls="freightFreightExportMenu">
                <i class="fa fa-plane-departure"></i><span> Freight Export</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $freightExportActive ? 'show' : '' }}" id="freightFreightExportMenu">

            </div>
        </div>

        {{-- Due Lists --}}
        <div class="nav-item-group">
            <div class="nav-section">Due Lists</div>
            <a href="#freightDueListMenu" class="nav-link {{ $dueListsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                aria-expanded="{{ $dueListsActive ? 'true' : 'false' }}" aria-controls="freightDueListMenu">
                <i class="fa fa-user-clock"></i><span> Due Lists</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $dueListsActive ? 'show' : '' }}" id="freightDueListMenu">
                <a href="{{ route('nas-freights.due-lists.customer') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.due-lists.customer') ? 'active' : '' }}">
                    <i class="fa fa-user-clock"></i> Customer Due
                </a>
                <a href="{{ route('nas-freights.due-lists.supplier') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.due-lists.supplier') ? 'active' : '' }}">
                    <i class="fa fa-truck-loading"></i> Supplier Due
                </a>
            </div>
        </div>

        {{-- Collections --}}
        <div class="nav-item-group">
            <div class="nav-section">Collections</div>
            <a href="#freightCollectionsMenu" class="nav-link {{ $collectionsActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $collectionsActive ? 'true' : 'false' }}"
                aria-controls="freightCollectionsMenu">
                <i class="fa fa-money-bill-wave"></i><span> Collections</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $collectionsActive ? 'show' : '' }}" id="freightCollectionsMenu">
                <a href="{{ route('nas-freights.money-receipts.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.money-receipts.*') ? 'active' : '' }}">
                    <i class="fa fa-money-bill-wave"></i> Money Receipts
                </a>
                <a href="{{ route('nas-freights.supplier-payments.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.supplier-payments.*') ? 'active' : '' }}">
                    <i class="fa fa-hand-holding-usd"></i> Supplier Payments
                </a>
            </div>
        </div>

        {{-- Reports --}}
        <div class="nav-item-group">
            <div class="nav-section">Reports</div>
            <a href="#freightReportsMenu" class="nav-link {{ $reportsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                aria-expanded="{{ $reportsActive ? 'true' : 'false' }}" aria-controls="freightReportsMenu">
                <i class="fa fa-chart-bar"></i><span> Reports</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $reportsActive ? 'show' : '' }}" id="freightReportsMenu">
                <a href="{{ route('nas-freights.reports.booking') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.reports.booking*') ? 'active' : '' }}">
                    <i class="fa fa-chart-bar"></i> Booking Report
                </a>
                <a href="{{ route('nas-freights.reports.party-bill-summary') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.reports.party-bill-summary*') ? 'active' : '' }}">
                    <i class="fa fa-file-alt"></i> Bill Summary
                </a>
                <a href="{{ route('nas-freights.reports.bill-details') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.reports.bill-details*') ? 'active' : '' }}">
                    <i class="fa fa-list-alt"></i> Bill Details
                </a>
            </div>
        </div>

        {{-- Fleet (single item — flat) --}}
        <div class="nav-item-group">
            <div class="nav-section">Fleet</div>
            <a href="{{ route('nas-freights.vehicles.index') }}"
                class="nav-link {{ request()->routeIs('nas-freights.vehicles.*') ? 'active' : '' }}">
                <i class="fa fa-truck"></i> Vehicles
            </a>
        </div>

        {{-- Stakeholders --}}
        <div class="nav-item-group">
            <div class="nav-section">Stakeholders</div>
            <a href="#freightStakeholdersMenu" class="nav-link {{ $stakeholdersActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $stakeholdersActive ? 'true' : 'false' }}"
                aria-controls="freightStakeholdersMenu">
                <i class="fa fa-users"></i><span> Stakeholders</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $stakeholdersActive ? 'show' : '' }}" id="freightStakeholdersMenu">
                <a href="{{ route('nas-freights.stakeholders.suppliers.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.stakeholders.suppliers.*') ? 'active' : '' }}">
                    <i class="fa fa-truck-loading"></i> Suppliers
                </a>
                <a href="{{ route('nas-freights.stakeholders.customers.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.stakeholders.customers.*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Customers
                </a>
            </div>
        </div>

        {{-- Import --}}
        <div class="nav-item-group">
            <div class="nav-section">Import</div>
            <a href="#freightImportMenu" class="nav-link {{ $importActive ? 'active' : '' }}" data-bs-toggle="collapse"
                aria-expanded="{{ $importActive ? 'true' : 'false' }}" aria-controls="freightImportMenu">
                <i class="fa fa-file-import"></i><span> Import</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $importActive ? 'show' : '' }}" id="freightImportMenu">
                <a href="{{ route('nas-freights.import.supplier-payments') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.supplier-payments*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Supplier Payments
                </a>
                <a href="{{ route('nas-freights.import.customer-bills') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.customer-bills*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Customer Bills
                </a>
                <a href="{{ route('nas-freights.import.bookings') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.bookings*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Bookings
                </a>
                <a href="{{ route('nas-freights.import.vehicles') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.vehicles*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Vehicles
                </a>
                <a href="{{ route('nas-freights.import.booking-updates') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.booking-updates*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Booking Updates
                </a>
                <a href="{{ route('nas-freights.import.customer-bill-summary') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.import.customer-bill-summary*') ? 'active' : '' }}">
                    <i class="fa fa-file-import"></i> Bill Summary
                </a>
            </div>
        </div>

        {{-- Settings --}}
        <div class="nav-item-group">
            <div class="nav-section">Settings</div>
            <a href="#freightSettingsMenu" class="nav-link {{ $settingsActive ? 'active' : '' }}"
                data-bs-toggle="collapse" aria-expanded="{{ $settingsActive ? 'true' : 'false' }}"
                aria-controls="freightSettingsMenu">
                <i class="fa fa-cog"></i><span> Settings</span>
                <i class="fa fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse {{ $settingsActive ? 'show' : '' }}" id="freightSettingsMenu">
                <a href="{{ route('nas-freights.settings.branches.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.branches.*') ? 'active' : '' }}">
                    <i class="fa fa-code-branch"></i> Branches
                </a>
                <a href="{{ route('nas-freights.settings.container-types.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.container-types.*') ? 'active' : '' }}">
                    <i class="fa fa-box"></i> Container Types
                </a>
                <a href="{{ route('nas-freights.settings.package-types.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.package-types.*') ? 'active' : '' }}">
                    <i class="fa fa-cube"></i> Package Types
                </a>
                <a href="{{ route('nas-freights.settings.overseas-agents.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.overseas-agents.*') ? 'active' : '' }}">
                    <i class="fa fa-globe"></i> Overseas Agents
                </a>
                <a href="{{ route('nas-freights.settings.shipping-carriers.index') }}"
                    class="nav-link ps-4 {{ request()->routeIs('nas-freights.settings.shipping-carriers.*') ? 'active' : '' }}">
                    <i class="fa fa-ship"></i> Shipping Carriers
                </a>
            </div>
        </div>
    </div>
@endsection
