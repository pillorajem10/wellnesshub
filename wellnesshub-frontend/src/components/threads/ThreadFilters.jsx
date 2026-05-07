import Input from '@/components/common/Input'
import Select from '@/components/common/Select'
import { THREAD_SORT_OPTIONS } from '@/utils/constants'

export default function ThreadFilters({ search, sort, onSearchChange, onSortChange }) {
  return (
    <div className="grid gap-4 md:grid-cols-2">
      <Input
        id="protocol-search"
        label="Search protocols"
        value={search}
        onChange={(event) => onSearchChange(event.target.value)}
        placeholder='Try "sleep" or "recovery"'
      />
      <Select
        id="protocol-sort"
        label="Sort"
        value={sort}
        onChange={(event) => onSortChange(event.target.value)}
        options={THREAD_SORT_OPTIONS}
      />
    </div>
  )
}
