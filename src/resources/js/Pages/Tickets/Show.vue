<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  ticket: { type: Object, required: true },
})

const prettyJson = (obj) => {
  try {
    return JSON.stringify(obj, null, 2)
  } catch {
    return String(obj ?? '')
  }
}
</script>

<template>
  <Head :title="`Ticket #${ticket.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Ticket #{{ ticket.id }}
        </h2>

        <Link href="/tickets" class="underline text-sm">
          Back to Tickets
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="space-y-1">
              <div class="text-2xl font-bold">
                {{ ticket.title }}
              </div>

              <div class="text-sm text-gray-600">
                Project: {{ ticket.project?.name ?? 'N/A' }}
                · Assignee: {{ ticket.assignee?.name ?? 'Unassigned' }}
                · Created: {{ ticket.created_at ?? '-' }}
              </div>
            </div>

            <div class="mt-6 space-y-2">
              <h3 class="text-lg font-semibold">Description</h3>
              <p class="whitespace-pre-wrap text-gray-800">{{ ticket.description }}</p>
            </div>

            <div class="mt-6 space-y-2" v-if="ticket.attachment?.original_name">
              <h3 class="text-lg font-semibold">Attachment</h3>
              <div class="text-sm text-gray-800">
                <div><span class="font-semibold">Name:</span> {{ ticket.attachment.original_name }}</div>
                <div><span class="font-semibold">MIME:</span> {{ ticket.attachment.mime ?? '-' }}</div>
              </div>
            </div>

            <div class="mt-6 space-y-2">
              <h3 class="text-lg font-semibold">Technical data</h3>
              <pre class="bg-gray-100 p-4 rounded overflow-auto text-sm">{{ prettyJson(ticket.detail?.technical_data) }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
