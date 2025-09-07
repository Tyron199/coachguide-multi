<template>
    <div class="min-h-screen bg-white">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    <span v-if="!signed">Review and Sign Contract</span>
                    <span v-else-if="isFullySigned">Contract Fully Executed</span>
                    <span v-else>Your Signature Recorded</span>
                </h1>
                <p class="text-gray-600 mt-2">
                    <span v-if="!signed">
                        Hello {{ client.name }}, please review the coaching agreement from {{ coach.name }} below and
                        provide your signature to continue.
                    </span>
                    <span v-else-if="isFullySigned">
                        This contract has been fully executed by both parties. You can download your signed copy below.
                    </span>
                    <span v-else>
                        Your signature has been recorded. The contract is now awaiting your coach's countersignature.
                    </span>
                </p>
            </div>

            <!-- Contract Preview -->
            <div class="mb-6 bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                <div class="h-[70vh]">
                    <iframe :src="previewUrl" class="w-full h-full border-0" sandbox="allow-same-origin"
                        title="Contract Preview"></iframe>
                </div>
            </div>

            <!-- Signature Section -->
            <div v-if="!signed" class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Client Signature Required</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ client.name }}, please sign in the box below to agree to
                        the terms of this
                        coaching contract with {{ coach.name }}.</p>
                </div>
                <div class="p-6">
                    <Form :action="store(token)" method="post" #default="{ processing, errors, recentlySuccessful }"
                        @success="onSuccess">
                        <div class="space-y-4">
                            <!-- Signature Pad -->
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="text-sm text-gray-600 mb-3">Sign inside the box below</div>
                                <div class="border-2 border-gray-300 rounded-md overflow-hidden bg-white">
                                    <canvas ref="canvasRef" class="w-full h-48 cursor-crosshair"></canvas>
                                </div>
                                <!-- Client name below signature -->
                                <div class="mt-3 pt-2 border-t border-gray-200">
                                    <div class="text-sm font-medium text-gray-700 text-center">
                                        {{ client.name }}
                                    </div>
                                    <div class="text-xs text-gray-500 text-center">
                                        Client Signature
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-3">
                                    By signing, you agree to the terms and conditions of this agreement.
                                </div>
                                <input type="hidden" name="signature" :value="signatureData" />
                            </div>

                            <!-- Error Messages -->
                            <div v-if="error" class="text-sm text-red-600">
                                {{ error }}
                            </div>
                            <div v-if="errors?.signature" class="text-sm text-red-600">
                                {{ errors.signature }}
                            </div>
                            <div v-if="recentlySuccessful" class="text-sm text-green-600">
                                Thank you! Your signature has been recorded successfully.
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 pt-6">
                            <Button type="button" variant="outline" @click="clearSignature"
                                class="border-gray-300 text-gray-700 hover:bg-gray-50">
                                <RotateCcw class="h-4 w-4" />
                                Clear
                            </Button>
                            <Button type="submit" :disabled="processing || isEmpty" class="min-w-32">
                                <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                                <PenTool v-else class="h-4 w-4" />
                                {{ processing ? 'Signing...' : 'Agree & Sign' }}
                            </Button>
                        </div>
                    </Form>
                </div>
            </div>

            <!-- Signed State -->
            <div v-else class="bg-white border border-gray-200 rounded-lg shadow-sm p-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                            <CheckCircle class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Your Signature Recorded</h3>
                            <p class="text-sm text-gray-600">
                                <span v-if="isFullySigned">
                                    Contract is now fully executed by both parties.
                                </span>
                                <span v-else>
                                    Waiting for your coach to countersign. You will receive a notification when the
                                    contract is fully executed.
                                </span>
                            </p>
                        </div>
                    </div>
                    <Button v-if="isFullySigned" asChild>
                        <a :href="pdfUrl" target="_blank" rel="noopener">
                            <Download class="h-4 w-4" />
                            Download PDF
                        </a>
                    </Button>
                    <div v-else class="flex items-center text-sm text-gray-500">
                        <Clock class="h-4 w-4 mr-2" />
                        Awaiting coach signature
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import SignaturePad from 'signature_pad'
import { Form } from '@inertiajs/vue3'
import { preview as previewAction, pdf as pdfAction, store } from '@/actions/App/Http/Controllers/Tenant/Client/ContractController'

import { Button } from '@/components/ui/button'
import { CheckCircle, Clock, Download, Loader2, PenTool, RotateCcw } from 'lucide-vue-next'

const props = defineProps<{
    token: string;
    contract: { id: number; status: number; is_fully_signed: boolean };
    client: { id: number; name: string; email: string };
    coach: { id: number; name: string };
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let pad: SignaturePad | null = null

const error = ref('')
const signatureData = ref('')
const isSignatureEmpty = ref(true)

const previewUrl = computed(() => previewAction(props.token).url)
const pdfUrl = computed(() => pdfAction(props.token).url)
const signed = computed(() => props.contract?.status >= 3)
const isFullySigned = computed(() => props.contract?.is_fully_signed)

const resize = () => {
    if (!canvasRef.value || !pad) return
    const ratio = Math.max(window.devicePixelRatio || 1, 1)
    const parent = canvasRef.value.parentElement as HTMLElement
    const width = parent.clientWidth
    const height = Math.max(192, Math.floor(parent.clientWidth * 0.4))
    canvasRef.value.width = width * ratio
    canvasRef.value.height = height * ratio
    canvasRef.value.style.width = `${width}px`
    canvasRef.value.style.height = `${height}px`
    const ctx = canvasRef.value.getContext('2d')!
    ctx.scale(ratio, ratio)
    pad.clear()
    // Update reactive state after clearing
    isSignatureEmpty.value = true
    signatureData.value = ''
}

onMounted(() => {
    if (canvasRef.value) {
        pad = new SignaturePad(canvasRef.value, {
            backgroundColor: 'rgba(255,255,255,1)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 2.5,
        })
        resize()
        window.addEventListener('resize', resize)

        pad.addEventListener('beginStroke', () => {
            // As soon as user starts drawing, button should be enabled
            isSignatureEmpty.value = false
        })

        pad.addEventListener('endStroke', () => {
            const isEmpty = pad!.isEmpty()
            isSignatureEmpty.value = isEmpty
            signatureData.value = isEmpty ? '' : pad!.toDataURL('image/png')
        })
    }
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', resize)
    pad?.off()
})

const isEmpty = computed(() => isSignatureEmpty.value)

const clearSignature = () => {
    pad?.clear()
    signatureData.value = ''
    isSignatureEmpty.value = true
}



const onSuccess = () => {
    console.log('[SignContract] form success - reloading page')
    // After successful sign, reload so server returns status >= 3 and UI hides pad
    window.location.reload()
}
</script>
