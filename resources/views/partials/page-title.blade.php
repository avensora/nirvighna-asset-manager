<div class="page-title-box">
    <div class="page-title-left">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Nirvighna</a></li>
            @if(isset($subtitle))
                <li class="breadcrumb-item">{{ $subtitle }}</li>
            @endif
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
    </div>
    <div class="page-title-right">
        <h4 class="page-title">{{ $title }}</h4>
    </div>
</div>
