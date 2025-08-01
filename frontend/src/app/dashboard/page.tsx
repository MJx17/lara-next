"use client"

import { useEffect, useState } from "react"

import { AppSidebar } from "@/components/app-sidebar"
import { ChartAreaInteractive } from "@/components/chart-area-interactive"
import { DataTable } from "@/components/data-table"
import { SectionCards } from "@/components/section-cards"
import { SiteHeader } from "@/components/site-header"
import {
  SidebarInset,
  SidebarProvider,
} from "@/components/ui/sidebar"

import { fetchPrivilegeAccessRequests } from "@/lib/login-request" // ← adjust if needed
import type { PrivilegeAccessRequest } from "@/lib/login-request"

export default function Page() {
  const [data, setData] = useState<PrivilegeAccessRequest[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const loadData = async () => {
      try {
        const response = await fetchPrivilegeAccessRequests()
        setData(response)
      } catch (error) {
        console.error("Failed to load data:", error)
      } finally {
        setLoading(false)
      }
    }

    loadData()
  }, [])

  return (
    <SidebarProvider
      style={
        {
          "--sidebar-width": "calc(var(--spacing) * 72)",
          "--header-height": "calc(var(--spacing) * 12)",
        } as React.CSSProperties
      }
    >
      <AppSidebar variant="inset" />
      <SidebarInset>
        <SiteHeader />
        <div className="flex flex-1 flex-col">
          <div className="@container/main flex flex-1 flex-col gap-2">
            <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
              <SectionCards />
              <div className="px-4 lg:px-6">
                <ChartAreaInteractive />
              </div>
              {loading ? (
                <div className="text-center py-6 text-muted-foreground">Loading...</div>
              ) : (
                <DataTable data={data} />
              )}
            </div>
          </div>
        </div>
      </SidebarInset>
    </SidebarProvider>
  )
}
