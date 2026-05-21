                @if($summaries->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 performance-summary-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%;">#</th>
                                    <th style="width: 18%;">Employee</th>
                                    <th style="width: 13%;">Branch</th>
                                    <th style="width: 15%;">Department</th>
                                    <th class="text-center" style="width: 10%;">Evaluations</th>
                                    <th class="text-center" style="width: 12%;">Overall Rating</th>
                                    <th class="text-center" style="width: 12%;">Performance</th>
                                    <th class="text-center" style="width: 12%;">Trend</th>
                                    <th class="text-center" style="width: 7%;">Action</th>
                                    <th class="text-center" style="width: 7%;">PDF</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($summaries as $summary)
                                    <tr>
                                        <td class="text-center">
                                            {{ ($summaries->currentPage() - 1) * $summaries->perPage() + $loop->iteration }}
                                        </td>

                                        <td>
                                            <div class="fw-semibold">{{ $summary['employee_name'] }}</div>
                                            <small class="text-secondary">{{ $summary['employee_email'] ?? 'No email' }}</small>
                                        </td>

                                        <td class="performance-summary-wrap-cell">{{ $summary['branch_name'] }}</td>
                                        <td class="performance-summary-wrap-cell performance-summary-department-cell">{{ $summary['department_name'] }}</td>

                                        <td class="text-center">
                                            <span class="fw-semibold">{{ $summary['total_evaluations'] }}</span>
                                        </td>

                                        <td class="text-center">
                                            <span class="performance-summary-score">
                                                {{ number_format($summary['average_score'], 2) }}%
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="performance-summary-pill performance-summary-pill-{{ $summary['performance_class'] }}">
                                                {{ $summary['performance_label'] }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="performance-summary-pill performance-summary-pill-{{ $summary['trend_class'] }}">
                                                {{ $summary['trend_label'] }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <a href="{{ route('hr.evaluation.performance-summary.show', ['employeeProfile' => $summary['employee_profile_id'], 'year' => $year, 'quarter' => $quarter]) }}"
                                               class="btn btn-outline-primary btn-sm rounded-pill performance-summary-view-btn">
                                                View
                                            </a>
                                        </td>

                                        <td class="text-center">
                                            <a href="{{ route('hr.evaluation.performance-summary.pdf', ['employeeProfile' => $summary['employee_profile_id'], 'year' => $year, 'quarter' => $quarter]) }}"
                                               class="btn btn-danger btn-sm performance-summary-pdf-btn"
                                               title="Download Q{{ $quarter }} {{ $year }} Performance Summary PDF"
                                               aria-label="Download Q{{ $quarter }} {{ $year }} Performance Summary PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M6.75 2.75h7.1l3.4 3.55v14.95H6.75V2.75Z" fill="#ffffff"/>
                                                    <path d="M13.85 2.75V6.3h3.4" stroke="#dc3545" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.95 9.4h6.1M8.95 11.6h6.1M8.95 13.8h4.7" stroke="#9ca3af" stroke-width="1.2" stroke-linecap="round"/>
                                                    <rect x="4.25" y="14.45" width="12.25" height="5.15" rx="1.05" fill="#dc3545"/>
                                                    <path d="M6.1 18.2v-2.55h1.05c.64 0 1.06.36 1.06.9 0 .55-.42.91-1.06.91h-.42v.74H6.1Zm.63-1.27h.36c.3 0 .47-.14.47-.38s-.17-.37-.47-.37h-.36v.75Zm2.01 1.27v-2.55h1.02c.84 0 1.4.5 1.4 1.27s-.56 1.28-1.4 1.28H8.74Zm.63-.54h.35c.49 0 .79-.28.79-.74s-.3-.73-.79-.73h-.35v1.47Zm2.28.54v-2.55h1.88v.53h-1.25v.53h1.12v.52h-1.12v.97h-.63Z" fill="#ffffff"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $summaries->links() }}
                    </div>
                @else
                    <div class="performance-summary-empty">
                        <i class="fas fa-chart-line fa-2x mb-3"></i>
                        <h5 class="mb-1">No performance summary found.</h5>
                        <p class="mb-0">
                            Submitted evaluations for the selected quarter will appear here.
                        </p>
                    </div>
                @endif
