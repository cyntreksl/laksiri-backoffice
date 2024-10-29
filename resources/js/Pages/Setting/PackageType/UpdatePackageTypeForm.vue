<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    packageType: {
        type: Object,
        default: () => {
        },
    },
});

const form = useForm({
    id: props.packageType.id,
    name: props.packageType.name,
    description: props.packageType.description,
});

const updatePackageType = () => {
    form.put(route("setting.package-types.update", props.packageType.id), {
        onSuccess: () => {
            router.visit(route("setting.package-types.index"));
            form.reset();
            push.success("Package Type Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Package Type">
        <template #header>Package Type</template>

        <!-- Breadcrumb -->
        <Breadcrumb :packageType="packageType"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <div class="card px-4 py-4 sm:px-5">
                <div>
                    <h2
                        class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Update Package Type
                    </h2>
                    <br/>
                </div>
                <form @submit.prevent="updatePackageType">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="col-span-1 sm:col-span-2">
                            <InputLabel value="Package Type"/>
                            <TextInput v-model="form.name" class="w-full" placeholder="Package Type"/>
                            <InputError :message="form.errors.name"/>
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <InputLabel value="Description"/>
                            <label class="relative flex">
                        <textarea
                            v-model="form.description"
                            class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Type Description..."
                            rows="4"
                        ></textarea>
                            </label>
                            <InputError :message="form.errors.description"/>
                        </div>
                    </div>
                    <br/>
                    <div class="flex col-span-2 justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            class="ms-3"
                            type="submit"
                        >
                            Update Package Type
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

