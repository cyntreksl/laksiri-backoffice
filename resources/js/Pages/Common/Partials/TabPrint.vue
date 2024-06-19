<script setup>
import Tab from "@/Components/Tab.vue";
</script>

<template>
    <Tab label="Print HBL & CR" name="tabPrint">
        <div>
            <p>
                Pellentesque pulvinar, sapien eget fermentum sodales, felis lacus
                viverra magna, id pulvinar odio metus non enim. Ut id augue
                interdum, ultrices felis eu, tincidunt libero.
            </p>
            <div class="flex space-x-2 pt-3">
                <a
                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                    href="#"
                >
                    Tag 1
                </a>
                <a
                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                    href="#"
                >
                    Tag 2
                </a>
            </div>

            <p class="pt-3 text-xs text-slate-400 dark:text-navy-300">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore
                dolore non atque?
            </p>
        </div>
    </Tab>
</template>
