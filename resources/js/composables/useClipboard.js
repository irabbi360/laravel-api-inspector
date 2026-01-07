import { useToast } from './useToast'

export function useClipboard() {
    const { showToast } = useToast()
    
    const fallbackCopyToClipboard = (text) => {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-999999px';
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      try {
          document.execCommand('copy');
          showToast('Response schema copied to clipboard!', 'success');
      } catch (err) {
          showToast('Failed to copy response schema', 'error');
          console.error('Fallback copy error:', err);
      }
      document.body.removeChild(textArea);
  };

  const copyToClipboard = (textContent) => {
      // Try modern Clipboard API first
      if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(textContent).then(() => {
              showToast('Response schema copied to clipboard!', 'success');
          }).catch((err) => {
              showToast('Failed to copy response schema', 'error');
              console.error('Clipboard API error:', err);
              fallbackCopyToClipboard(textContent);
          });
      } else {
          // Fallback for older browsers or non-secure contexts
          fallbackCopyToClipboard(textContent);
      }
  };

  return {
    copyToClipboard,
  }
}
