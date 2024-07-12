<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<script>
export default {
  props: {
    instances: Array, // Definisci la proprietà 'instances' per ricevere i dati dal controller
  },
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">Active Istances</div>
                    <table class="table" style="width: 100%; color: white;">
                      <thead>
                        <tr>
                          <th scope="col">Id</th>
                          <th scope="col">Name</th>
                          <th scope="col">License Key</th>
                          <th scope="col">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="instance in instances" :key="instance.id">
                          <th scope="row" style="text-align: center;">{{instance.id}}</th>
                          <td style="text-align: center;">{{ instance.license_name }}</td>
                          <td style="text-align: center;">{{ instance.license_key }}</td>
                          <td style="text-align: center;">{{ instance.status }}</td>
                        </tr>
                      </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
