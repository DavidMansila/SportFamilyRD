// Forma unica de un deporte en el frontend.
//
// HomeVIew y DirectorioView comparten el mismo cache de seccion ('directorio'),
// y antes cada una tenia su propio mapeo. El del Home no copiaba 'category':
// si se entraba al Home y despues a Deportes (dentro de los 5 minutos del
// cache), Deportes reutilizaba esos datos sin categoria, todos los botones
// contaban 0 y solo quedaba "Todas". Por eso el mapeo vive aqui y lo usan las dos.
export function mapSport(s) {
    const image = String(s.image || '');
    return {
        id: s.id,
        name: s.name,
        region: s.region,
        type: s.type,
        popularity: s.popularity,
        category: s.category ?? null,
        // En la base de datos las rutas vienen sin "/" inicial: sin normalizar,
        // el navegador las resolveria relativas a la ruta actual.
        image: !image || image.startsWith('http') || image.startsWith('/') ? s.image : `/${image}`,
        shortDescription: s.short_description ?? s.shortDescription,
        description: s.description,
        requirements: s.requirements || [],
        places: s.places || [],
    };
}

// true si el cache se puede usar tal cual. Descarta listas vacias y las
// guardadas con el mapeo viejo del Home (sin la clave 'category'), que pueden
// seguir en el sessionStorage de quien ya tenia la pestaña abierta.
export function cacheDeportesValido(cached) {
    return Array.isArray(cached)
        && cached.length > 0
        && cached.every((s) => s && 'category' in s);
}
