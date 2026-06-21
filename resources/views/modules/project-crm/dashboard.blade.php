<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        <div class="row">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">CRM Dashboard</h4>
                            <p class="text-secondary mb-0">WMC CRM. Manage your customer relationships here.</p>
                        </div>

                        <a href="{{ route('crm.pipeline') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-columns me-1"></i> View Pipeline
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row">
            <div class="col-md-4 col-lg-2">
                <div class="card rounded-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Total Leads</p>
                        <h3 class="mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card rounded-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">New Leads</p>
                        <h3 class="mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card rounded-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Contacted</p>
                        <h3 class="mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card rounded-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Proposals</p>
                        <h3 class="mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card rounded-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Won Deals</p>
                        <h3 class="mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card rounded-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Follow-ups Today</p>
                        <h3 class="mb-0">0</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-lg-8">
                <div class="card rounded-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Leads</h5>
                        <p class="text-secondary mb-0">Latest prospects and customer inquiries.</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Lead / Company</th>
                                        <th>Contact Person</th>
                                        <th>Status</th>
                                        <th>Next Follow-up</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">
                                            No CRM leads yet. Leads CRUD will be added in Step 2.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card rounded-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upcoming Follow-ups</h5>
                        <p class="text-secondary mb-0">Scheduled calls, meetings, and updates.</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center text-secondary py-4">
                            No follow-ups yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>