<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  projects: Array,
  assignees: Array,
})

const form = useForm({
  project_id: props.projects?.[0]?.id ?? '',
  user_id: props.assignees?.[0]?.id ?? '',
  title: '',
  description: '',
  attachment: null,
})

function submit() {
  form.post('/', { forceFormData: true })
}
</script>

<template>
  <div style="max-width: 760px; margin: 0 auto; padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
      <h1 style="font-size: 24px; font-weight: 700;">Create Ticket</h1>
      <Link href="/">Back</Link>
    </div>

    <form @submit.prevent="submit" style="display:grid; gap: 12px;">
      <div>
        <label>Project</label>
        <select v-model="form.project_id" style="display:block; width:100%; padding:8px;">
          <option value="" disabled>Select a project</option>
          <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
        <div v-if="form.errors.project_id" style="color:#b91c1c;">{{ form.errors.project_id }}</div>
      </div>

      <div>
        <label>Assignee (responsible)</label>
        <select v-model="form.user_id" style="display:block; width:100%; padding:8px;">
          <option value="" disabled>Select a user</option>
          <option v-for="u in assignees" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
        <div v-if="form.errors.user_id" style="color:#b91c1c;">{{ form.errors.user_id }}</div>
      </div>

      <div>
        <label>Title</label>
        <input v-model="form.title" type="text" style="display:block; width:100%; padding:8px;" />
        <div v-if="form.errors.title" style="color:#b91c1c;">{{ form.errors.title }}</div>
      </div>

      <div>
        <label>Description (TicketDetail)</label>
        <textarea v-model="form.description" rows="6" style="display:block; width:100%; padding:8px;"></textarea>
        <div v-if="form.errors.description" style="color:#b91c1c;">{{ form.errors.description }}</div>
      </div>

      <div>
        <label>Attachment (optional: .txt or .json)</label>
        <input
          type="file"
          accept=".txt,.json,text/plain,application/json"
          @change="e => form.attachment = e.target.files?.[0] ?? null"
        />
        <div v-if="form.errors.attachment" style="color:#b91c1c;">{{ form.errors.attachment }}</div>
      </div>

      <button type="submit" :disabled="form.processing" style="padding:10px 14px;">
        {{ form.processing ? 'Saving…' : 'Create' }}
      </button>
    </form>
  </div>
</template>
