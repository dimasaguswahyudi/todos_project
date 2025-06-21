<script setup lang="ts">
import { ref } from "vue";
import { Button } from "~/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "~/components/ui/card";
import { Input } from "~/components/ui/input";
import { Label } from "~/components/ui/label";
import {
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "~/components/ui/form";

import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";
import { useRouter } from "vue-router";

const formSchema = toTypedSchema(
  z.object({
    email: z
      .string({ required_error: "Email wajib diisi" })
      .email("Email tidak valid"),
    password: z
      .string({ required_error: "Password wajib diisi" })
      .min(8, "Password harus memiliki panjang minimal 8 karakter"),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const loading = ref<boolean>(false);
const errorMessage = ref<string | null>(null);
const router = useRouter();

const onSubmit = form.handleSubmit(async (values) => {
  loading.value = true;
  errorMessage.value = null;
  try {
    const res = await fetch("http://127.0.0.1:8000/api/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(values),
    });

    const data = await res.json();

    if (res.ok) {
      localStorage.setItem("token", data.token);
      localStorage.setItem("user", JSON.stringify(data.data));
      alert("Login successful!");
      router.push("/todos");
    } else {
      errorMessage.value = data.message || "Login Gagal!";
    }
  } catch (err) {
    errorMessage.value = "Terjadi kesalahan!, Silakan coba lagi.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div
    class="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10"
  >
    <Card class="w-full max-w-sm">
      <CardHeader>
        <CardTitle class="text-2xl"> Login </CardTitle>
        <CardDescription>
          Enter your email below to login to your account.
        </CardDescription>
      </CardHeader>
      <form @submit="onSubmit">
        <CardContent class="grid gap-4 mb-4">
          <div class="grid gap-2">
            <FormField v-slot="{ componentField }" name="email">
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <Input
                    type="text"
                    placeholder="loremipsum@gmail.com"
                    v-bind="componentField"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </div>
          <div class="grid gap-2">
            <FormField v-slot="{ componentField }" name="password">
              <FormItem>
                <FormLabel>Password</FormLabel>
                <FormControl>
                  <Input
                    type="password"
                    placeholder="••••••••"
                    v-bind="componentField"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </div>
          <div v-if="errorMessage" class="text-red-500 text-sm">
            {{ errorMessage }}
          </div>
        </CardContent>
        <CardFooter>
          <Button class="w-full" :disabled="loading">
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign in</span>
          </Button>
        </CardFooter>
      </form>
    </Card>
  </div>
</template>
