<script setup lang="ts">
import { ref, onMounted, reactive } from "vue";
import { useRouter } from "vue-router";

import { ScrollArea } from "~/components/ui/scroll-area";
import { Separator } from "~/components/ui/separator";
import { Input } from "~/components/ui/input";
import { Button } from "~/components/ui/button";
import { Icon } from "@iconify/vue";
definePageMeta({
  middleware: "auth",
});

const router = useRouter();
const todoInput = ref("");
const user = reactive({
  id: 0,
  name: "",
  email: "",
});
const todos = ref<{ id: number; title: string; completed: boolean }[]>([]);
const loading = ref<boolean>(false);
const errorMessage = ref<string | null>(null);

const fetchTodos = async () => {
  loading.value = true;
  errorMessage.value = null;
  try {
    const token = localStorage.getItem("token");
    const res = await fetch("http://127.0.0.1:8000/api/todos", {
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    });
    const data = await res.json();
    if (res.ok) {
      todos.value = data.data;
    } else {
      errorMessage.value = data.message || "Gagal memuat data";
    }
  } catch (err) {
    errorMessage.value = "Terjadi kesalahan saat memuat data";
  } finally {
    loading.value = false;
  }
};

const storeTodos = async () => {
  if (!todoInput.value.trim()) return;
  loading.value = true;
  errorMessage.value = null;
  try {
    const token = localStorage.getItem("token");
    const res = await fetch("http://127.0.0.1:8000/api/todos", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: JSON.stringify({ title: todoInput.value }),
    });
    const data = await res.json();
    if (res.ok) {
      todos.value.push(data.data);
      todoInput.value = "";
    } else {
      errorMessage.value = data.message || "Gagal menambahkan todo";
    }
  } catch (err) {
    errorMessage.value = "Terjadi kesalahan saat menambahkan todo";
  } finally {
    loading.value = false;
  }
};

const updateTodo = async (id: number, title: string, completed: string) => {
  loading.value = true;
  errorMessage.value = null;
  try {
    const token = localStorage.getItem("token");
    const res = await fetch(`http://127.0.0.1:8000/api/todos/${id}`, {
      method: "PUT",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: JSON.stringify({ title, completed }),
    });
    const data = await res.json();
    if (res.ok) {
      const index = todos.value.findIndex((todo) => todo.id === id);
      if (index !== -1) {
        todos.value[index] = data.data;
      }
    } else {
      errorMessage.value = data.message || "Gagal memperbarui todo";
    }
  } catch (err) {
    errorMessage.value = "Terjadi kesalahan saat memperbarui todo";
  } finally {
    loading.value = false;
  }
};

const deleteTodos = async (id: number) => {
  loading.value = true;
  errorMessage.value = null;
  try {
    const token = localStorage.getItem("token");
    const res = await fetch(`http://127.0.0.1:8000/api/todos/${id}`, {
      method: "DELETE",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    });
    if (res.ok) {
      todos.value = todos.value.filter((todo) => todo.id !== id);
    } else {
      errorMessage.value = "Gagal menghapus todo";
    }
  } catch (err) {
    errorMessage.value = "Terjadi kesalahan saat menghapus todo";
  } finally {
    loading.value = false;
  }
};

const logout = async () => {
  loading.value = true;
  errorMessage.value = null;
  try {
    const token = localStorage.getItem("token");
    const res = await fetch("http://127.0.0.1:8000/api/logout", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    });
    if (res.ok) {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      router.push("/");
    } else {
      errorMessage.value = "Gagal logout";
    }
  } catch (error) {
    errorMessage.value = "Terjadi kesalahan saat logout";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchTodos();
  const storedUser = JSON.parse(localStorage.getItem("user"));
  if (storedUser) {
    user.id = storedUser.id;
    user.name = storedUser.name;
    user.email = storedUser.email;
  }
});
</script>

<template>
  <div
    class="flex min-h-screen h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-50 via-white to-gray-200 px-4 py-8"
  >
    <div
      class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8 flex flex-col items-center"
    >
      <h1 class="text-3xl font-extrabold text-gray-900">
        Hallo, {{ user.name }}
      </h1>
      <p class="text-base text-gray-500 mb-6 text-center">
        Daftar tugas yang perlu diselesaikan:
      </p>
      <Input
        v-model="todoInput"
        type="text"
        placeholder="Tambahkan tugas baru..."
        class="w-full mb-3 focus:ring-2 focus:ring-primary transition"
        @keyup.enter="storeTodos"
        :disabled="loading"
      />
      <ScrollArea
        class="w-full max-h-[45vh] rounded-xl border border-gray-200 bg-gray-50 mb-3"
      >
        <div class="p-4">
          <h4 class="mb-4 text-lg font-semibold text-gray-800">Todo</h4>
          <div v-if="loading" class="text-center text-gray-500 py-10">
            Loading...
          </div>
          <div v-else-if="errorMessage" class="text-center text-red-500 py-10">
            {{ errorMessage }}
          </div>
          <template v-else>
            <div v-if="!todos.length" class="text-center text-gray-400">
              Belum ada todo
            </div>
            <div v-for="todo in todos" :key="todo.id">
              <div
                class="flex items-center justify-between flex-wrap gap-2 p-4 rounded-xl border transition-colors duration-200 shadow-sm"
                :class="{
                  'line-through text-gray-400 border-green-500 bg-green-50 hover:bg-green-100 hover:text-white':
                    todo.completed,
                  'border-gray-200 bg-white hover:bg-primary/10 hover:text-primary':
                    !todo.completed,
                }"
              >
                <span
                  @click="
                    !todo.completed && updateTodo(todo.id, todo.title, true)
                  "
                  class="truncate text-base font-medium text-gray-700"
                  :class="{
                    'cursor-pointer': !todo.completed,
                  }"
                >
                  {{ todo.title }}
                </span>
                <Button
                  type="button"
                  variant="ghost"
                  aria-label="Hapus tugas"
                  @click="deleteTodos(todo.id)"
                  :disabled="loading"
                >
                  <Icon icon="radix-icons:cross-2" width="15" height="15" />
                </Button>
              </div>
              <Separator class="my-2" />
            </div>
          </template>
        </div>
      </ScrollArea>
      <Button
        type="button"
        variant="ghost"
        aria-label="Hapus tugas"
        @click="logout()"
      >
        <Icon icon="radix-icons:exit" width="15" height="15" /> Keluar
      </Button>
    </div>
  </div>
</template>
