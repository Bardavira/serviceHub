<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

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
  form.post('/tickets', { forceFormData: true })
}
</script>

<template>
  <Head title="Create Ticket" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Create Ticket
        </h2>

        <Link href="/tickets" class="underline text-sm">
          Back to Tickets
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="grid gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Project</label>
                <select v-model="form.project_id" class="mt-1 block w-full rounded border-gray-300">
                  <option value="" disabled>Select a project</option>
                  <option v-for="p in projects" :key="p.id" :value="p.id">
                    {{ p.name }}
                  </option>
                </select>
                <div v-if="form.errors.project_id" class="text-sm text-red-700 mt-1">
                  {{ form.errors.project_id }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Assignee</label>
                <select v-model="form.user_id" class="mt-1 block w-full rounded border-gray-300">
                  <option value="" disabled>Select a user</option>
                  <option v-for="u in assignees" :key="u.id" :value="u.id">
                    {{ u.name }}
                  </option>
                </select>
                <div v-if="form.errors.user_id" class="text-sm text-red-700 mt-1">
                  {{ form.errors.user_id }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input v-model="form.title" type="text" class="mt-1 block w-full rounded border-gray-300" />
                <div v-if="form.errors.title" class="text-sm text-red-700 mt-1">
                  {{ form.errors.title }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="6" class="mt-1 block w-full rounded border-gray-300"></textarea>
                <div v-if="form.errors.description" class="text-sm text-red-700 mt-1">
                  {{ form.errors.description }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Attachment (.txt or .json)</label>
                <input
                  type="file"
                  accept=".txt,.json,text/plain,application/json"
                  class="mt-1 block w-full text-sm"
                  @change="e => form.attachment = e.target.files?.[0] ?? null"
                />
                <div v-if="form.errors.attachment" class="text-sm text-red-700 mt-1">
                  {{ form.errors.attachment }}
                </div>
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-60"
                >
                  {{ form.processing ? 'Saving…' : 'Create' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
