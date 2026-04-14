<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center !px-8 !py-3 bg-[#CCC2DC] border border-transparent rounded-full font-bold text-xs !text-black uppercase tracking-widest hover:bg-[#E6E1E5] focus:outline-none focus:ring-2 focus:ring-[#CCC2DC] transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
