<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
})
</script>

<template>
  <Head title="Home" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Home
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 space-y-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                ServiceHub
              </h1>

              <p class="mt-2 text-gray-700 leading-relaxed">
                ServiceHub is a small service-order management application built as a technical challenge.
                It models a real-world scenario where a company manages projects, and projects contain tickets
                assigned to responsible users.
              </p>
            </div>

            <div class="space-y-3">
              <h2 class="text-lg font-semibold text-gray-900">
                Main flow
              </h2>

              <ul class="list-disc ps-5 space-y-2 text-gray-700">
                <li>Create a Ticket through the UI (Inertia + Vue).</li>
                <li>Optionally upload an attachment (.txt or .json).</li>
                <li>A background Job processes that attachment and enriches the ticket technical details.</li>
                <li>The assignee receives a notification after the ticket is processed.</li>
              </ul>
            </div>

            <div class="space-y-3">
              <h2 class="text-lg font-semibold text-gray-900">
                Domain model
              </h2>

              <ul class="list-disc ps-5 space-y-2 text-gray-700">
                <li>A Company has many Projects.</li>
                <li>A Project has many Tickets.</li>
                <li>A Ticket has one TicketDetail.</li>
                <li>A User has one UserProfile and interacts with Tickets.</li>
              </ul>
            </div>

            <p class="text-xs text-gray-500">
              This repository is intended for evaluation of backend modeling, queue processing, and basic UI flow.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
