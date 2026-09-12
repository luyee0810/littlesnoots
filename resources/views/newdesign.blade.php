{{-- New scrapbook homepage. Ported from public/newdesign-demo.html, section by
     section. The pets section is wired to live data; the rest is still static
     copy until there are models behind it. --}}
<x-nd.layout title="Little Snoots — Find your companion">
    <x-nd.nav />
    <x-nd.hero />
    <x-nd.pets :pets="$featured" />
    <x-nd.process />
    <x-nd.stories />
    <x-nd.guides />
    <x-nd.testimonials />
    <x-nd.footer />
</x-nd.layout>
