<script setup>
import { Link } from '@inertiajs/vue3'

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
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">
          Ticket #{{ ticket.id }} — {{ ticket.title }}
        </h1>
        <div class="text-sm text-gray-600">
          Project: {{ ticket.project?.name ?? 'N/A' }}
          · Assignee: {{ ticket.assignee?.name ?? 'Unassigned' }}
          · Created: {{ ticket.created_at ?? '-' }}
        </div>
      </div>

      <Link href="/" class="underline">
        Back to list
      </Link>
    </div>

    <div class="space-y-2">
      <h2 class="text-lg font-semibold">Description</h2>
      <p class="whitespace-pre-wrap">{{ ticket.description }}</p>
    </div>

    <div class="space-y-2" v-if="ticket.attachment?.original_name">
      <h2 class="text-lg font-semibold">Attachment</h2>
      <div class="text-sm">
        <div><strong>Name:</strong> {{ ticket.attachment.original_name }}</div>
        <div><strong>MIME:</strong> {{ ticket.attachment.mime ?? '-' }}</div>
      </div>
    </div>

    <div class="space-y-2">
      <h2 class="text-lg font-semibold">Technical data</h2>
      <pre class="bg-gray-100 p-4 rounded overflow-auto text-sm">{{ prettyJson(ticket.detail?.technical_data) }}</pre>
    </div>
  </div>
</template>
