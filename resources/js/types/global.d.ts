/**
 * Tipos globais para a aplicação
 */

/**
 * Helper route do Ziggy para navegação com rotas nomeadas
 */
declare function route(
    name: string,
    params?: Record<string, any>,
    absolute?: boolean
): string;

declare global {
    interface Window {
        route: typeof route;
    }
}

export {};
