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
    exceptionName: {
        type: Object,
        default: () => {
        },
    },
});

const form = useForm({
    id: props.exceptionName.id,
    name: props.exceptionName.name,
});

const updateExceptionName = () => {
    form.put(route("setting.exception-names.update", props.exceptionName.id), {
        onSuccess: () => {
            router.visit(route("setting.exception-names.index"));
            form.reset();
            push.success("Exception Name Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Exception Name">
        <template #header>Exception Name</template>

        <!-- Breadcrumb -->
        <Breadcrumb :exceptionName="exceptionName"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <div class="card px-4 py-4 sm:px-5">
                <div>
                    <h2
                        class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Update Exception Name
                    </h2>
                    <br/>
                </div>
                <form @submit.prevent="updateExceptionName">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="col-span-1 sm:col-span-2">
                            <InputLabel value="Exception Name"/>
                            <TextInput v-model="form.name" class="w-full" placeholder="Exception Name"/>
                            <InputError :message="form.errors.name"/>
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
                            Update Exception Name
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

