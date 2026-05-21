<x-guest-layout>
<div class="gradient">
    <div class="container">
        <img src="{{ asset('images/error/404.png') }}" class="img-fluid mb-4 w-50" alt="">
        <h2 class="mb-0 mt-4 text-white">Oops! Restricted Access.</h2>
        <p class="mt-2 text-white">You do not have permission to access this page.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a class="btn bg-white text-primary d-inline-flex align-items-center" href="{{ route('dashboard') }}">
                Back to Home
            </a>
        </div>
    </div>
    <div class="box">
        <div class="c xl-circle">
            <div class="c lg-circle">
                <div class="c md-circle">
                    <div class="c sm-circle">
                        <div class="c xs-circle">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-guest-layout>