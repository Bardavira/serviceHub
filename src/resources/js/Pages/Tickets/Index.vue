<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ tickets: Object })
</script>

<template>
  <div style="max-width: 900px; margin: 0 auto; padding: 24px;">
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 16px;">
      <h1 style="font-size: 24px; font-weight: 700;">Tickets</h1>
      <Link href="/tickets/create">Create Ticket</Link>
    </div>

    <div v-if="tickets.data.length === 0">No tickets yet.</div>

    <div v-else style="display:grid; gap: 10px;">
      <div v-for="t in tickets.data" :key="t.id" style="border:1px solid #ddd; padding:12px;">
        <div style="font-weight:700;">#{{ t.id }} — {{ t.title }}</div>
        <div>Project: {{ t.project?.name }}</div>
        <div>Assignee: {{ t.assignee?.name }}</div>
        <div style="opacity:0.7;">{{ t.created_at }}</div>
      </div>
    </div>
  </div>
</template>