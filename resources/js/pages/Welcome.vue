<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Helios · Solar Software" />
    <div class="flex min-h-svh flex-col bg-background text-foreground">
        <header
            class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-6 py-6 sm:px-10"
        >
            <span class="brand-wordmark text-xl font-semibold">HELIOS</span>
            <Button as-child variant="outline">
                <Link :href="$page.props.auth.user ? dashboard() : login()">
                    {{
                        $page.props.auth.user
                            ? 'Ir al resumen'
                            : 'Iniciar sesión'
                    }}
                </Link>
            </Button>
        </header>
        <main
            class="mx-auto grid w-full max-w-7xl flex-1 items-center gap-12 px-6 py-12 sm:px-10 lg:grid-cols-2 lg:gap-20 lg:py-24"
        >
            <div>
                <h1
                    class="max-w-xl text-4xl leading-[1.12] font-semibold tracking-tight sm:text-5xl lg:text-6xl"
                >
                    Tu operación solar, con claridad.
                </h1>
                <p
                    class="mt-6 max-w-lg text-lg leading-relaxed text-muted-foreground"
                >
                    Prospectos, recibos CFE e ingeniería solar en un mismo
                    espacio de trabajo.
                </p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <Button as-child size="lg"
                        ><Link
                            :href="
                                $page.props.auth.user ? dashboard() : login()
                            "
                            >Entrar a Helios</Link
                        ></Button
                    >
                    <Button
                        v-if="!$page.props.auth.user"
                        as-child
                        size="lg"
                        variant="outline"
                        ><Link :href="register()">Crear cuenta</Link></Button
                    >
                </div>
            </div>
            <div
                class="flex aspect-square max-h-[480px] items-center justify-center overflow-hidden rounded-3xl border bg-[#F1F7F6] p-10 text-[#08221B]"
            >
                <div class="w-full text-center">
                    <AppLogoIcon class="mx-auto aspect-[1.4] w-3/4 max-w-64" />
                    <p class="brand-wordmark mt-8 text-4xl sm:text-5xl">
                        HELIOS
                    </p>
                    <p class="mt-3 text-xs tracking-[0.24em] text-[#354F47]">
                        SOLAR SOFTWARE
                    </p>
                </div>
            </div>
        </main>
        <footer
            class="mx-auto w-full max-w-7xl border-t px-6 py-6 text-sm text-muted-foreground sm:px-10"
        >
            Helios Solar Software
        </footer>
    </div>
</template>
