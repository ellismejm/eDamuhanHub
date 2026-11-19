@extends('layouts.app')

@section('title', 'Our Developers')

@section('content')
    <div class="mb-4">
        <span class="dh-badge mb-2">The Project Team</span>
        <h1 class="dh-page-title">Meet Our Developers</h1>
        <p class="dh-page-subtitle mb-0">
            The people behind eDamuhanHub, a rice weed in the Philippines information and database management system.
        </p>
    </div>

    <div class="row g-3">
        @foreach ([
            ['number' => '01', 'name' => 'Meynard Ellis Molina'],
            ['number' => '02', 'name' => 'John Sebastian Mangilin'],
            ['number' => '03', 'name' => 'Princess Ellieza Hipolito'],
        ] as $developer)
            <div class="col-12 col-md-6 col-xl-4">
                <article class="dh-card h-100 p-4 text-center">
                    <div class="dh-stat-icon mx-auto mb-3"
                         style="width: 76px; height: 76px; border-radius: 50%; background: #eaf1e5; color: #395b32; font-size: 24px;">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="small text-muted mb-2">Developer {{ $developer['number'] }}</div>
                    <h2 class="dh-section-title mb-2">{{ $developer['name'] }}</h2>
                    <p class="text-muted mb-0">eDamuhanHub Project Developer</p>
                </article>
            </div>
        @endforeach
    </div>

    
@endsection
