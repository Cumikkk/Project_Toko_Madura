export const load = async (module) => {
    const v = window.APP_VERSION || '1';
    const mod = await import(`./${module}?v=${v}`);

    return mod.default || mod;
}