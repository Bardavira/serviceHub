<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({ tickets: Object })
</script>

<template>
  <Head title="Tickets" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Tickets
        </h2>

        <Link href="/tickets/create" class="underline text-sm">
          Create Ticket
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="tickets.data.length === 0">
              No tickets yet.
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="t in tickets.data"
                :key="t.id"
                class="border border-gray-200 rounded p-4"
              >
                <div class="flex items-baseline justify-between gap-4">
                  <div class="font-semibold">
                    #{{ t.id }} — {{ t.title }}
                  </div>

                  <Link :href="`/tickets/${t.id}`" class="underline text-sm">
                    View
                  </Link>
                </div>

                <div class="text-sm text-gray-700 mt-2">
                  <div>Project: {{ t.project?.name }}</div>
                  <div>Assignee: {{ t.assignee?.name }}</div>
                  <div class="text-gray-500">{{ t.created_at }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
