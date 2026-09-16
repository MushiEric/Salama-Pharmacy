import { create } from 'zustand';

type UiState = {
    sidebarOpen: boolean;
    activeModal: string | null;
    selectedPrinterId: string | null;
    setSidebarOpen: (open: boolean) => void;
    setActiveModal: (id: string | null) => void;
    setSelectedPrinterId: (id: string | null) => void;
};

export const useUiStore = create<UiState>((set) => ({
    sidebarOpen: true,
    activeModal: null,
    selectedPrinterId: null,
    setSidebarOpen: (sidebarOpen) => set({ sidebarOpen }),
    setActiveModal: (activeModal) => set({ activeModal }),
    setSelectedPrinterId: (selectedPrinterId) => set({ selectedPrinterId }),
}));
