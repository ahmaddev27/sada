import { ref } from 'vue'
import { defineStore } from 'pinia'

export interface ConfirmOptions {
    title: string
    message?: string
    confirmText?: string
    cancelText?: string
    dangerous?: boolean
}

export const useConfirmStore = defineStore('confirm', () => {
    const open        = ref(false)
    const title       = ref('')
    const message     = ref('')
    const confirmText = ref('تأكيد')
    const cancelText  = ref('إلغاء')
    const dangerous   = ref(false)

    // kept outside reactive state — closures are not serialisable
    let _resolve: ((value: boolean) => void) | null = null

    function ask(opts: ConfirmOptions): Promise<boolean> {
        title.value       = opts.title
        message.value     = opts.message     ?? ''
        confirmText.value = opts.confirmText ?? 'تأكيد'
        cancelText.value  = opts.cancelText  ?? 'إلغاء'
        dangerous.value   = opts.dangerous   ?? false
        open.value        = true

        return new Promise<boolean>((resolve) => {
            _resolve = resolve
        })
    }

    function doConfirm() {
        _resolve?.(true)
        _resolve  = null
        open.value = false
    }

    function doCancel() {
        _resolve?.(false)
        _resolve  = null
        open.value = false
    }

    return { open, title, message, confirmText, cancelText, dangerous, ask, doConfirm, doCancel }
})
