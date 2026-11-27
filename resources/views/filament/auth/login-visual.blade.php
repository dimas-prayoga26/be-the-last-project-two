{{-- Left half of the login page: the studio photo from Studio profile › About image. --}}
<aside
    class="tlp-auth-visual"
    @if ($imageUrl) style="background-image: url('{{ $imageUrl }}')" @endif
    aria-hidden="true"
>
    <img src="{{ asset('brand/tlp-logo-primary-white.svg') }}" alt="" class="tlp-auth-visual-logo" />
</aside>
