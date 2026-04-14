<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center !px-8 !py-3 bg-[#D0BCFF] border border-transparent rounded-full font-bold text-xs !text-black uppercase tracking-widest hover:bg-[#EADDFF] focus:bg-[#EADDFF] active:bg-[#D0BCFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
