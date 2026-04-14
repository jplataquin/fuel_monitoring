<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center !px-8 !py-3 bg-[#F2B8B5] border border-transparent rounded-full font-bold text-xs !text-black uppercase tracking-widest hover:bg-[#F9DEDC] focus:outline-none focus:ring-2 focus:ring-[#F2B8B5] transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
